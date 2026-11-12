<?php

namespace App\Services;

use App\Exceptions\Payments\LeadUnavailableException;
use App\Exceptions\Payments\PaymentFailedException;
use App\Exceptions\Payments\SquareNotConfiguredException;
use App\Exceptions\Payments\SquarePaymentException;
use App\Models\LeadPurchase;
use App\Models\User;
use App\Services\Payments\SquareClient;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Sells a lead to exactly one operator and charges them through Square.
 *
 * Order of operations (see SQUARE_PAYMENT_PLAN.md):
 *  1. reserve the lead (unique `lead_lock`) so two operators can never both pay,
 *  2. charge outside any DB transaction using a stable idempotency key,
 *  3. finalise the purchase from Square's answer (paid, failed, or still pending).
 *
 * Every state transition is idempotent and only acts on `pending` rows, so the
 * controller, the webhook and the reconcile command can finish a purchase in any
 * order.
 */
class LeadPurchaseService
{
    public function __construct(
        private SquareClient $square,
        private LeadService $leads,
    ) {}

    /**
     * Reserve the lead, charge the card and unlock the lead when Square confirms.
     *
     * Returns a `paid` purchase, or a `pending` one when the outcome is not known
     * yet (the webhook or `payments:reconcile` settles it).
     *
     * @throws SquareNotConfiguredException
     * @throws LeadUnavailableException
     * @throws PaymentFailedException
     */
    public function checkout(User $user, string $source, int $leadId, string $sourceToken): LeadPurchase
    {
        $this->square->assertConfigured();

        $own = $user->leadPurchases()
            ->where('leadable_type', $source)
            ->where('leadable_id', $leadId)
            ->whereIn('status', [LeadPurchase::STATUS_PAID, LeadPurchase::STATUS_PENDING])
            ->first();

        if ($own !== null) {
            return $own;
        }

        $purchase = $this->reserve($user, $source, $leadId);

        try {
            $payment = $this->square->createPayment($this->paymentPayload($purchase, $user, $source, $sourceToken));
        } catch (SquarePaymentException $exception) {
            return $this->handleChargeException($purchase, $exception);
        }

        return $this->applySquarePayment($purchase, $payment);
    }

    /**
     * Apply a Square payment object to a purchase (webhook, reconcile, checkout).
     *
     * Idempotent: only `pending` purchases change. `COMPLETED` unlocks the lead
     * after amount, currency and location are re-validated; `FAILED` / `CANCELED`
     * releases it; anything else just records the Square ids.
     *
     * @param  array<string, mixed>  $payment
     */
    public function applySquarePayment(LeadPurchase $purchase, array $payment): LeadPurchase
    {
        return DB::transaction(function () use ($purchase, $payment): LeadPurchase {
            $locked = LeadPurchase::query()->lockForUpdate()->findOrFail($purchase->id);

            if (! $locked->isPending()) {
                return $locked;
            }

            $status = (string) ($payment['status'] ?? '');

            if ($status === 'COMPLETED') {
                return $this->completeIfValid($locked, $payment);
            }

            if (in_array($status, ['FAILED', 'CANCELED'], true)) {
                return $this->failLocked($locked, 'PAYMENT_'.$status, 'PAYMENT_STATUS', null, $status);
            }

            $locked->forceFill([
                'square_payment_id' => $payment['id'] ?? $locked->square_payment_id,
                'square_status' => $status !== '' ? $status : $locked->square_status,
            ])->save();

            return $locked;
        });
    }

    /**
     * Release a pending purchase after a definitive failure.
     */
    public function markFailed(
        LeadPurchase $purchase,
        ?string $code,
        ?string $category = null,
        ?string $detail = null,
    ): LeadPurchase {
        return DB::transaction(function () use ($purchase, $code, $category, $detail): LeadPurchase {
            $locked = LeadPurchase::query()->lockForUpdate()->findOrFail($purchase->id);

            if (! $locked->isPending()) {
                return $locked;
            }

            return $this->failLocked($locked, $code, $category, $detail);
        });
    }

    /**
     * Whether Square's payment matches what we asked to charge.
     *
     * @param  array<string, mixed>  $payment
     */
    public function paymentMatchesPurchase(LeadPurchase $purchase, array $payment): bool
    {
        $money = $payment['amount_money'] ?? [];
        $locationId = $payment['location_id'] ?? null;

        return (int) ($money['amount'] ?? -1) === $purchase->amount_cents
            && strtoupper((string) ($money['currency'] ?? '')) === strtoupper($purchase->currency)
            && ($locationId === null || $locationId === $this->square->locationId());
    }

    /**
     * @throws LeadUnavailableException
     */
    private function reserve(User $user, string $source, int $leadId): LeadPurchase
    {
        $leadKey = LeadPurchase::leadKey($source, $leadId);
        $price = (int) config('leads.price');

        try {
            return DB::transaction(function () use ($user, $source, $leadId, $leadKey, $price): LeadPurchase {
                $this->leads->modelClass($source)::query()
                    ->whereIn('status', LeadService::LISTED_STATUSES)
                    ->lockForUpdate()
                    ->findOrFail($leadId);

                if (LeadPurchase::query()->where('lead_lock', $leadKey)->exists()) {
                    throw new LeadUnavailableException;
                }

                return $user->leadPurchases()->create([
                    'leadable_type' => $source,
                    'leadable_id' => $leadId,
                    'price' => $price,
                    'status' => LeadPurchase::STATUS_PENDING,
                    'lead_lock' => $leadKey,
                    'amount_cents' => $price * 100,
                    'currency' => $this->square->currency(),
                    'payment_provider' => LeadPurchase::PROVIDER_SQUARE,
                    'idempotency_key' => (string) Str::uuid(),
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            throw new LeadUnavailableException;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentPayload(LeadPurchase $purchase, User $user, string $source, string $sourceToken): array
    {
        return [
            'idempotency_key' => $purchase->idempotency_key,
            'source_id' => $sourceToken,
            'location_id' => $this->square->locationId(),
            'amount_money' => [
                'amount' => $purchase->amount_cents,
                'currency' => $purchase->currency,
            ],
            'reference_id' => 'LP-'.$purchase->id,
            'note' => 'Lead '.LeadService::publicIdFor($source, $purchase->leadable_id)." / user #{$user->id}",
            'buyer_email_address' => $user->email,
            'autocomplete' => true,
        ];
    }

    /**
     * @throws PaymentFailedException
     */
    private function handleChargeException(LeadPurchase $purchase, SquarePaymentException $exception): LeadPurchase
    {
        if ($exception->isOutcomeUnknown()) {
            Log::warning('square.payment.unknown_outcome', [
                'purchase_id' => $purchase->id,
                'user_id' => $purchase->user_id,
                'code' => $exception->errorCode,
                'category' => $exception->category,
                'http_status' => $exception->httpStatus,
            ]);

            return $purchase->refresh();
        }

        $this->markFailed($purchase, $exception->errorCode, $exception->category, $exception->detail);

        if ($exception->isConfigurationProblem()) {
            Log::error('square.payment.configuration_problem', [
                'purchase_id' => $purchase->id,
                'code' => $exception->errorCode,
                'category' => $exception->category,
            ]);
        }

        throw PaymentFailedException::fromSquare($exception);
    }

    /**
     * @param  array<string, mixed>  $payment
     */
    private function completeIfValid(LeadPurchase $locked, array $payment): LeadPurchase
    {
        if (! $this->paymentMatchesPurchase($locked, $payment)) {
            Log::critical('square.payment.mismatch', [
                'purchase_id' => $locked->id,
                'square_payment_id' => $payment['id'] ?? null,
                'expected_cents' => $locked->amount_cents,
                'square_amount' => $payment['amount_money']['amount'] ?? null,
            ]);

            $locked->forceFill([
                'square_payment_id' => $payment['id'] ?? $locked->square_payment_id,
                'square_status' => 'COMPLETED',
                'square_payload' => $this->sanitizePayload($payment),
            ])->save();

            return $locked;
        }

        $card = $payment['card_details']['card'] ?? [];

        $locked->forceFill([
            'status' => LeadPurchase::STATUS_PAID,
            'square_payment_id' => $payment['id'] ?? null,
            'square_order_id' => $payment['order_id'] ?? null,
            'square_status' => 'COMPLETED',
            'square_receipt_url' => $payment['receipt_url'] ?? null,
            'square_receipt_number' => $payment['receipt_number'] ?? null,
            'card_brand' => $card['card_brand'] ?? null,
            'card_last4' => $card['last_4'] ?? null,
            'square_payload' => $this->sanitizePayload($payment),
            'failure_code' => null,
            'failure_category' => null,
            'failure_message' => null,
            'paid_at' => now(),
            'purchased_at' => now(),
        ])->save();

        return $locked;
    }

    private function failLocked(
        LeadPurchase $locked,
        ?string $code,
        ?string $category,
        ?string $detail,
        ?string $squareStatus = null,
    ): LeadPurchase {
        $locked->forceFill([
            'status' => LeadPurchase::STATUS_FAILED,
            'lead_lock' => null,
            'square_status' => $squareStatus ?? $locked->square_status,
            'failure_code' => $code !== null ? Str::limit($code, 100, '') : null,
            'failure_category' => $category !== null ? Str::limit($category, 100, '') : null,
            'failure_message' => $detail !== null ? Str::limit($detail, 500, '') : null,
        ])->save();

        return $locked;
    }

    /**
     * Keep an audit copy of Square's payment without card identifiers we do not need.
     *
     * @param  array<string, mixed>  $payment
     * @return array<string, mixed>
     */
    private function sanitizePayload(array $payment): array
    {
        unset(
            $payment['card_details']['card']['fingerprint'],
            $payment['card_details']['card']['cardholder_name'],
            $payment['buyer_email_address'],
        );

        return $payment;
    }
}
