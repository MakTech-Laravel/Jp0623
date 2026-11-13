<?php

namespace App\Services;

use App\Exceptions\Payments\PaymentFailedException;
use App\Exceptions\Payments\SquareNotConfiguredException;
use App\Exceptions\Payments\SquarePaymentException;
use App\Models\MembershipPayment;
use App\Models\OperatorMembership;
use App\Models\User;
use App\Services\Payments\SquareClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MembershipService
{
    public function __construct(
        private MembershipSettingsService $settings,
        private SquareClient $square,
    ) {}

    public function ensureFor(User $user): OperatorMembership
    {
        $existing = $user->membership;

        if ($existing !== null) {
            if ($this->settings->isPaid()) {
                $existing->markExpiredPaidPeriodIfNeeded();
            }

            return $existing->refresh();
        }

        return OperatorMembership::query()->create([
            'user_id' => $user->id,
            'status' => $this->settings->isPaid()
                ? OperatorMembership::STATUS_REQUIRED
                : OperatorMembership::STATUS_ACTIVE_FREE,
            'monthly_price' => $this->settings->monthlyPrice(),
        ]);
    }

    public function userCanAccessMarketplace(User $user): bool
    {
        if ($this->settings->isFree()) {
            return true;
        }

        return $this->ensureFor($user)->hasValidPaidPeriod();
    }

    /**
     * @return array<string, mixed>
     */
    public function pagePayload(User $user): array
    {
        $settings = $this->settings->current();
        $membership = $this->ensureFor($user);
        $allowsAccess = $this->userCanAccessMarketplace($user);

        return [
            'settings' => [
                'mode' => $settings->mode,
                'monthly_price' => (int) $settings->monthly_price,
                'is_paid' => $settings->isPaid(),
            ],
            'membership' => [
                'status' => $membership->status,
                'monthly_price' => $membership->monthly_price,
                'card_brand' => $membership->card_brand,
                'card_last4' => $membership->card_last4,
                'current_period_end' => $membership->current_period_end?->toIso8601String(),
                'next_billing_at' => $membership->next_billing_at?->toIso8601String(),
                'needs_payment' => $settings->isPaid() && $membership->needsPayment(),
                'allows_access' => $allowsAccess,
            ],
            'square' => $settings->isPaid() ? $this->square->publicConfig() : null,
        ];
    }

    /**
     * Charge the first month (or recover past_due) with a Square card nonce.
     *
     * @throws SquareNotConfiguredException
     * @throws PaymentFailedException
     */
    public function checkout(User $user, string $sourceToken): MembershipPayment
    {
        $this->square->assertConfigured();

        if ($this->settings->isFree()) {
            throw new PaymentFailedException('Membership is currently free.');
        }

        $membership = $this->ensureFor($user);
        $price = $this->settings->monthlyPrice();

        $customerId = $membership->square_customer_id
            ?: $this->square->createCustomer([
                'idempotency_key' => (string) Str::uuid(),
                'given_name' => $user->name,
                'email_address' => $user->email,
                'reference_id' => 'user-'.$user->id,
            ]);

        $card = $this->square->createCard([
            'idempotency_key' => (string) Str::uuid(),
            'source_id' => $sourceToken,
            'card' => [
                'customer_id' => $customerId,
            ],
        ]);

        $cardId = (string) ($card['id'] ?? '');
        $cardBrand = $card['card_brand'] ?? ($card['card']['card_brand'] ?? null);
        $cardLast4 = $card['last_4'] ?? ($card['card']['last_4'] ?? null);

        if ($cardId === '') {
            throw new PaymentFailedException('Unable to save the card on file.');
        }

        $payment = MembershipPayment::query()->create([
            'user_id' => $user->id,
            'operator_membership_id' => $membership->id,
            'price' => $price,
            'amount_cents' => $price * 100,
            'currency' => $this->square->currency(),
            'status' => MembershipPayment::STATUS_PENDING,
            'payment_provider' => MembershipPayment::PROVIDER_SQUARE,
            'idempotency_key' => (string) Str::uuid(),
            'card_brand' => is_string($cardBrand) ? $cardBrand : null,
            'card_last4' => is_string($cardLast4) ? $cardLast4 : null,
        ]);

        try {
            $squarePayment = $this->square->createPayment([
                'idempotency_key' => $payment->idempotency_key,
                'source_id' => $cardId,
                'customer_id' => $customerId,
                'autocomplete' => true,
                'location_id' => $this->square->locationId(),
                'amount_money' => [
                    'amount' => $payment->amount_cents,
                    'currency' => $payment->currency,
                ],
                'reference_id' => 'MB-'.$payment->id,
                'note' => 'Operator membership',
            ]);
        } catch (SquarePaymentException $exception) {
            $payment->forceFill([
                'status' => MembershipPayment::STATUS_FAILED,
                'failure_code' => $exception->errorCode,
                'failure_category' => $exception->category,
                'failure_message' => $exception->detail ?? $exception->getMessage(),
            ])->save();

            throw PaymentFailedException::fromSquare($exception);
        }

        return $this->finalizeSuccessfulPayment(
            $payment,
            $membership,
            $squarePayment,
            $customerId,
            $cardId,
            is_string($cardBrand) ? $cardBrand : null,
            is_string($cardLast4) ? $cardLast4 : null,
            $price,
        );
    }

    /**
     * @param  array<string, mixed>  $squarePayment
     */
    public function finalizeSuccessfulPayment(
        MembershipPayment $payment,
        OperatorMembership $membership,
        array $squarePayment,
        string $customerId,
        string $cardId,
        ?string $cardBrand,
        ?string $cardLast4,
        int $price,
    ): MembershipPayment {
        return DB::transaction(function () use (
            $payment,
            $membership,
            $squarePayment,
            $customerId,
            $cardId,
            $cardBrand,
            $cardLast4,
            $price,
        ): MembershipPayment {
            $lockedPayment = MembershipPayment::query()->lockForUpdate()->findOrFail($payment->id);
            $lockedMembership = OperatorMembership::query()->lockForUpdate()->findOrFail($membership->id);

            if ($lockedPayment->isPaid()) {
                return $lockedPayment;
            }

            $status = (string) ($squarePayment['status'] ?? '');

            if ($status !== 'COMPLETED') {
                $lockedPayment->forceFill([
                    'status' => MembershipPayment::STATUS_FAILED,
                    'square_payment_id' => $squarePayment['id'] ?? null,
                    'square_status' => $status !== '' ? $status : null,
                    'square_payload' => $squarePayment,
                    'failure_code' => 'PAYMENT_'.$status,
                    'failure_message' => 'Membership payment was not completed.',
                ])->save();

                throw new PaymentFailedException('Membership payment was not completed.');
            }

            $cardDetails = $squarePayment['card_details']['card'] ?? [];
            $periodStart = now();
            $periodEnd = $periodStart->copy()->addDays(30);

            $lockedPayment->forceFill([
                'status' => MembershipPayment::STATUS_PAID,
                'square_payment_id' => $squarePayment['id'] ?? null,
                'square_order_id' => $squarePayment['order_id'] ?? null,
                'square_status' => $status,
                'square_receipt_url' => $squarePayment['receipt_url'] ?? null,
                'square_receipt_number' => $squarePayment['receipt_number'] ?? null,
                'card_brand' => $cardBrand ?: ($cardDetails['card_brand'] ?? $lockedPayment->card_brand),
                'card_last4' => $cardLast4 ?: ($cardDetails['last_4'] ?? $lockedPayment->card_last4),
                'square_payload' => $squarePayment,
                'paid_at' => now(),
            ])->save();

            $lockedMembership->forceFill([
                'status' => OperatorMembership::STATUS_ACTIVE,
                'monthly_price' => $price,
                'square_customer_id' => $customerId,
                'square_card_id' => $cardId,
                'card_brand' => $lockedPayment->card_brand,
                'card_last4' => $lockedPayment->card_last4,
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
                'next_billing_at' => $periodEnd,
                'canceled_at' => null,
            ])->save();

            return $lockedPayment->refresh();
        });
    }
}
