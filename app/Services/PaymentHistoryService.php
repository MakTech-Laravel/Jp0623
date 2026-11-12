<?php

namespace App\Services;

use App\Models\CoffeeServiceRequest;
use App\Models\LeadPurchase;
use App\Models\User;
use App\Models\VendingRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Builds operator billing invoices and the admin payments list from lead purchases.
 */
class PaymentHistoryService
{
    /**
     * Paid purchases for the operator's billing page (newest first).
     *
     * @return list<array{
     *     id: string,
     *     location: string|null,
     *     date: string,
     *     status: string,
     *     description: string,
     *     amount: float,
     *     qty: int,
     *     receipt_url: string|null,
     *     card_brand: string|null,
     *     card_last4: string|null
     * }>
     */
    public function invoicesFor(User $user): array
    {
        return $user->leadPurchases()
            ->paid()
            ->with('leadable')
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (LeadPurchase $purchase): bool => $purchase->leadable !== null)
            ->map(fn (LeadPurchase $purchase): array => $this->mapInvoice($purchase))
            ->values()
            ->all();
    }

    /**
     * Latest paid card on file for display only (no stored payment method).
     *
     * @return array{brand: string, last4: string}|null
     */
    public function latestPaidCardFor(User $user): ?array
    {
        $purchase = $user->leadPurchases()
            ->paid()
            ->whereNotNull('card_last4')
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->first();

        if ($purchase === null || $purchase->card_last4 === null) {
            return null;
        }

        return [
            'brand' => (string) ($purchase->card_brand ?: 'Card'),
            'last4' => (string) $purchase->card_last4,
        ];
    }

    /**
     * Admin payments index.
     *
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateForAdmin(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 10,
    ): LengthAwarePaginator {
        $search = trim((string) $search);
        $status = filled($status) ? (string) $status : null;

        /** @var LengthAwarePaginator<int, LeadPurchase> $paginator */
        $paginator = LeadPurchase::query()
            ->with(['user', 'leadable'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $this->applyAdminSearch($query, $search);
            })
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $mapped = Collection::make($paginator->items())
            ->map(fn (LeadPurchase $purchase): array => $this->mapAdminPayment($purchase))
            ->all();

        $paginator->setCollection(Collection::make($mapped));

        return $paginator;
    }

    /**
     * @return array{
     *     id: string,
     *     location: string|null,
     *     date: string,
     *     status: string,
     *     description: string,
     *     amount: float,
     *     qty: int,
     *     receipt_url: string|null,
     *     card_brand: string|null,
     *     card_last4: string|null
     * }
     */
    private function mapInvoice(LeadPurchase $purchase): array
    {
        $lead = $purchase->leadable;
        $source = (string) $purchase->leadable_type;
        $location = null;

        if ($lead instanceof VendingRequest || $lead instanceof CoffeeServiceRequest) {
            $location = filled($lead->business_name)
                ? (string) $lead->business_name
                : trim($lead->city.' '.$lead->state);
        }

        $amountCents = $purchase->amount_cents ?: ((int) $purchase->price * 100);

        return [
            'id' => 'LP-'.$purchase->id,
            'location' => filled($location) ? $location : null,
            'date' => ($purchase->paid_at ?? $purchase->purchased_at ?? $purchase->created_at)->format('M j, Y'),
            'status' => 'Paid',
            'description' => 'Location lead ('.$source.')',
            'amount' => round($amountCents / 100, 2),
            'qty' => 1,
            'receipt_url' => $purchase->square_receipt_url,
            'card_brand' => $purchase->card_brand,
            'card_last4' => $purchase->card_last4,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapAdminPayment(LeadPurchase $purchase): array
    {
        $lead = $purchase->leadable;
        $leadName = null;
        $leadPublicId = LeadService::publicIdFor(
            (string) $purchase->leadable_type,
            (int) $purchase->leadable_id,
        );

        if ($lead instanceof VendingRequest || $lead instanceof CoffeeServiceRequest) {
            $leadName = filled($lead->business_name)
                ? (string) $lead->business_name
                : trim($lead->city.', '.$lead->state);
        }

        $amountCents = $purchase->amount_cents ?: ((int) $purchase->price * 100);

        return [
            'id' => $purchase->id,
            'invoice_id' => 'LP-'.$purchase->id,
            'operator_name' => $purchase->user?->name,
            'operator_email' => $purchase->user?->email,
            'lead_public_id' => $leadPublicId,
            'lead_name' => $leadName,
            'lead_source' => $purchase->leadable_type,
            'amount' => round($amountCents / 100, 2),
            'currency' => $purchase->currency,
            'status' => $purchase->status,
            'payment_provider' => $purchase->payment_provider,
            'card_brand' => $purchase->card_brand,
            'card_last4' => $purchase->card_last4,
            'square_payment_id' => $purchase->square_payment_id,
            'receipt_url' => $purchase->square_receipt_url,
            'paid_at' => $purchase->paid_at?->toIso8601String(),
            'paid_at_label' => $purchase->paid_at?->format('M j, Y g:i A'),
            'created_at' => $purchase->created_at?->toIso8601String(),
        ];
    }

    /**
     * @param  Builder<LeadPurchase>  $query
     */
    private function applyAdminSearch(Builder $query, string $search): void
    {
        $normalizedId = null;

        if (preg_match('/^LP-(\d+)$/i', $search, $matches) === 1) {
            $normalizedId = (int) $matches[1];
        } elseif (ctype_digit($search)) {
            $normalizedId = (int) $search;
        }

        $query->where(function (Builder $query) use ($search, $normalizedId): void {
            $query->where('square_payment_id', 'like', "%{$search}%")
                ->orWhere('square_receipt_number', 'like', "%{$search}%")
                ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });

            if ($normalizedId !== null) {
                $query->orWhere('id', $normalizedId);
            }
        });
    }
}
