<?php

namespace App\Services;

use App\Exceptions\Payments\SquarePaymentException;
use App\Models\MembershipPayment;
use App\Models\OperatorMembership;
use App\Services\Payments\SquareClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MembershipBillingService
{
    public function __construct(
        private MembershipSettingsService $settings,
        private SquareClient $square,
    ) {}

    /**
     * Charge due memberships. Returns [charged, failed, skipped].
     *
     * @return array{charged: int, failed: int, skipped: int}
     */
    public function billDue(): array
    {
        $charged = 0;
        $failed = 0;
        $skipped = 0;

        if ($this->settings->isFree()) {
            return compact('charged', 'failed', 'skipped');
        }

        if (! $this->square->isConfigured()) {
            Log::warning('membership.billing.square_not_configured');

            return compact('charged', 'failed', 'skipped');
        }

        $due = OperatorMembership::query()
            ->where('status', OperatorMembership::STATUS_ACTIVE)
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', now())
            ->whereNotNull('square_customer_id')
            ->whereNotNull('square_card_id')
            ->orderBy('id')
            ->get();

        foreach ($due as $membership) {
            try {
                $this->chargeMembership($membership);
                $charged++;
            } catch (SquarePaymentException $exception) {
                $this->markPastDue($membership, $exception);
                $failed++;
            } catch (\Throwable $exception) {
                Log::error('membership.billing.unexpected', [
                    'membership_id' => $membership->id,
                    'message' => $exception->getMessage(),
                ]);
                $this->markPastDue($membership, null, $exception->getMessage());
                $failed++;
            }
        }

        return compact('charged', 'failed', 'skipped');
    }

    /**
     * @throws SquarePaymentException
     */
    public function chargeMembership(OperatorMembership $membership): MembershipPayment
    {
        $this->square->assertConfigured();

        $price = $this->settings->monthlyPrice();
        $user = $membership->user;

        $payment = MembershipPayment::query()->create([
            'user_id' => $membership->user_id,
            'operator_membership_id' => $membership->id,
            'price' => $price,
            'amount_cents' => $price * 100,
            'currency' => $this->square->currency(),
            'status' => MembershipPayment::STATUS_PENDING,
            'payment_provider' => MembershipPayment::PROVIDER_SQUARE,
            'idempotency_key' => (string) Str::uuid(),
            'card_brand' => $membership->card_brand,
            'card_last4' => $membership->card_last4,
        ]);

        try {
            $squarePayment = $this->square->createPayment([
                'idempotency_key' => $payment->idempotency_key,
                'source_id' => $membership->square_card_id,
                'customer_id' => $membership->square_customer_id,
                'autocomplete' => true,
                'location_id' => $this->square->locationId(),
                'amount_money' => [
                    'amount' => $payment->amount_cents,
                    'currency' => $payment->currency,
                ],
                'reference_id' => 'MB-'.$payment->id,
                'note' => 'Operator membership renewal',
                'buyer_email_address' => $user?->email,
            ]);
        } catch (SquarePaymentException $exception) {
            $payment->forceFill([
                'status' => MembershipPayment::STATUS_FAILED,
                'failure_code' => $exception->errorCode,
                'failure_category' => $exception->category,
                'failure_message' => $exception->detail ?? $exception->getMessage(),
            ])->save();

            throw $exception;
        }

        $status = (string) ($squarePayment['status'] ?? '');

        if ($status !== 'COMPLETED') {
            $payment->forceFill([
                'status' => MembershipPayment::STATUS_FAILED,
                'square_payment_id' => $squarePayment['id'] ?? null,
                'square_status' => $status !== '' ? $status : null,
                'square_payload' => $squarePayment,
                'failure_code' => 'PAYMENT_'.$status,
                'failure_message' => 'Membership renewal was not completed.',
            ])->save();

            throw new SquarePaymentException(
                category: 'PAYMENT_STATUS',
                errorCode: 'PAYMENT_'.$status,
                detail: 'Membership renewal was not completed.',
            );
        }

        return DB::transaction(function () use ($payment, $membership, $squarePayment, $price): MembershipPayment {
            $lockedPayment = MembershipPayment::query()->lockForUpdate()->findOrFail($payment->id);
            $lockedMembership = OperatorMembership::query()->lockForUpdate()->findOrFail($membership->id);

            $card = $squarePayment['card_details']['card'] ?? [];
            $periodStart = $lockedMembership->current_period_end?->copy() ?? now();
            $periodEnd = $periodStart->copy()->addMonth();

            $lockedPayment->forceFill([
                'status' => MembershipPayment::STATUS_PAID,
                'square_payment_id' => $squarePayment['id'] ?? null,
                'square_order_id' => $squarePayment['order_id'] ?? null,
                'square_status' => 'COMPLETED',
                'square_receipt_url' => $squarePayment['receipt_url'] ?? null,
                'square_receipt_number' => $squarePayment['receipt_number'] ?? null,
                'card_brand' => $card['card_brand'] ?? $lockedPayment->card_brand,
                'card_last4' => $card['last_4'] ?? $lockedPayment->card_last4,
                'square_payload' => $squarePayment,
                'paid_at' => now(),
            ])->save();

            $lockedMembership->forceFill([
                'status' => OperatorMembership::STATUS_ACTIVE,
                'monthly_price' => $price,
                'card_brand' => $lockedPayment->card_brand,
                'card_last4' => $lockedPayment->card_last4,
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
                'next_billing_at' => $periodEnd,
            ])->save();

            return $lockedPayment->refresh();
        });
    }

    private function markPastDue(
        OperatorMembership $membership,
        ?SquarePaymentException $exception = null,
        ?string $fallbackMessage = null,
    ): void {
        $membership->forceFill([
            'status' => OperatorMembership::STATUS_PAST_DUE,
        ])->save();

        Log::warning('membership.billing.past_due', [
            'membership_id' => $membership->id,
            'user_id' => $membership->user_id,
            'code' => $exception?->errorCode,
            'message' => $exception?->detail ?? $fallbackMessage,
        ]);
    }
}
