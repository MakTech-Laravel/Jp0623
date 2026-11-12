<?php

namespace App\Services\Payments;

use App\Models\LeadPurchase;
use App\Models\PaymentEvent;
use App\Services\LeadPurchaseService;

/**
 * Applies verified Square webhook events to lead purchases.
 *
 * Only `payment.created` / `payment.updated` change anything; other event types
 * are stored and ignored. All state changes go through
 * {@see LeadPurchaseService::applySquarePayment()}, which is idempotent, so the
 * webhook can race with the checkout request or the reconcile command safely.
 */
class SquareWebhookHandler
{
    public function __construct(private LeadPurchaseService $purchases) {}

    /**
     * @return string One of the `PaymentEvent::OUTCOME_*` constants.
     */
    public function handle(PaymentEvent $event): string
    {
        return match ($event->type) {
            'payment.created', 'payment.updated' => $this->handlePayment($event),
            default => PaymentEvent::OUTCOME_IGNORED,
        };
    }

    private function handlePayment(PaymentEvent $event): string
    {
        $payment = $event->payload['data']['object']['payment'] ?? null;

        if (! is_array($payment)) {
            return PaymentEvent::OUTCOME_IGNORED;
        }

        $purchase = $this->findPurchase($payment, $event->payload['data']['id'] ?? null);

        if ($purchase === null) {
            return PaymentEvent::OUTCOME_UNMATCHED;
        }

        $status = (string) ($payment['status'] ?? '');

        if ($status === 'COMPLETED'
            && $purchase->isPending()
            && ! $this->purchases->paymentMatchesPurchase($purchase, $payment)) {
            // Records the payload and logs it critically; the purchase stays pending.
            $this->purchases->applySquarePayment($purchase, $payment);

            return PaymentEvent::OUTCOME_MISMATCH;
        }

        $wasPending = $purchase->isPending();
        $updated = $this->purchases->applySquarePayment($purchase, $payment);

        return $wasPending && ! $updated->isPending()
            ? PaymentEvent::OUTCOME_APPLIED
            : PaymentEvent::OUTCOME_IGNORED;
    }

    /**
     * Match by Square payment id first, then by our `LP-{id}` reference.
     *
     * @param  array<string, mixed>  $payment
     */
    private function findPurchase(array $payment, mixed $eventObjectId): ?LeadPurchase
    {
        $paymentId = $payment['id'] ?? (is_string($eventObjectId) ? $eventObjectId : null);

        if (is_string($paymentId) && $paymentId !== '') {
            $byPayment = LeadPurchase::query()->where('square_payment_id', $paymentId)->first();

            if ($byPayment !== null) {
                return $byPayment;
            }
        }

        $reference = (string) ($payment['reference_id'] ?? '');

        if (preg_match('/^LP-(\d+)$/', $reference, $matches) !== 1) {
            return null;
        }

        return LeadPurchase::query()
            ->whereKey((int) $matches[1])
            ->where('payment_provider', LeadPurchase::PROVIDER_SQUARE)
            ->first();
    }
}
