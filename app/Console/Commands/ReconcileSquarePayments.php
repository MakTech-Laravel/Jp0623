<?php

namespace App\Console\Commands;

use App\Exceptions\Payments\SquarePaymentException;
use App\Models\LeadPurchase;
use App\Services\LeadPurchaseService;
use App\Services\Payments\SquareClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

#[Signature('payments:reconcile {--min-age=5 : Only look at purchases pending for at least this many minutes} {--give-up-after=60 : Release purchases Square never heard of after this many minutes}')]
#[Description('Settle lead purchases stuck in "pending" by asking Square what happened to the payment')]
class ReconcileSquarePayments extends Command
{
    public function handle(SquareClient $square, LeadPurchaseService $purchases): int
    {
        if (! $square->isConfigured()) {
            $this->warn('Square is not configured; nothing to reconcile.');

            return self::SUCCESS;
        }

        $minAge = max(1, (int) $this->option('min-age'));
        $giveUpAfter = max($minAge + 1, (int) $this->option('give-up-after'));

        $pending = LeadPurchase::query()
            ->where('status', LeadPurchase::STATUS_PENDING)
            ->where('payment_provider', LeadPurchase::PROVIDER_SQUARE)
            ->where('created_at', '<=', now()->subMinutes($minAge))
            ->oldest()
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No pending lead purchases to reconcile.');

            return self::SUCCESS;
        }

        $recentPayments = null;
        $settled = 0;
        $released = 0;
        $stillPending = 0;

        foreach ($pending as $purchase) {
            $payment = $this->findPayment($square, $purchase, $pending, $recentPayments);

            if ($payment !== null) {
                $updated = $purchases->applySquarePayment($purchase, $payment);

                $updated->isPending() ? $stillPending++ : $settled++;

                continue;
            }

            if ($recentPayments !== null && $purchase->created_at->lte(now()->subMinutes($giveUpAfter))) {
                $purchases->markFailed(
                    $purchase,
                    'NOT_FOUND_AT_SQUARE',
                    'RECONCILE',
                    "No Square payment with reference LP-{$purchase->id} after {$giveUpAfter} minutes.",
                );

                Log::warning('square.reconcile.released', ['purchase_id' => $purchase->id]);
                $released++;

                continue;
            }

            $stillPending++;
        }

        $this->info("Checked {$pending->count()} pending purchase(s): {$settled} settled, {$released} released, {$stillPending} still pending.");

        return self::SUCCESS;
    }

    /**
     * Find Square's payment for a purchase: by id when known, otherwise by
     * matching our `LP-{id}` reference in a (lazily fetched, shared) payment list.
     *
     * @param  Collection<int, LeadPurchase>  $pending
     * @param  list<array<string, mixed>>|null  $recentPayments
     * @return array<string, mixed>|null
     */
    private function findPayment(
        SquareClient $square,
        LeadPurchase $purchase,
        Collection $pending,
        ?array &$recentPayments,
    ): ?array {
        try {
            if ($purchase->square_payment_id !== null) {
                $payment = $square->getPayment($purchase->square_payment_id);

                return $payment !== [] ? $payment : null;
            }

            $recentPayments ??= $square->listPayments(
                $pending->min('created_at')->copy()->subMinutes(10),
                now()->addMinute(),
            );
        } catch (SquarePaymentException $exception) {
            Log::warning('square.reconcile.lookup_failed', [
                'purchase_id' => $purchase->id,
                'code' => $exception->errorCode,
                'http_status' => $exception->httpStatus,
            ]);

            return null;
        }

        foreach ($recentPayments as $payment) {
            if (($payment['reference_id'] ?? null) === 'LP-'.$purchase->id) {
                return $payment;
            }
        }

        return null;
    }
}
