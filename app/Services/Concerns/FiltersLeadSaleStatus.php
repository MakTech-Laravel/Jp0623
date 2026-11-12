<?php

namespace App\Services\Concerns;

use App\Models\CoffeeServiceRequest;
use App\Models\LeadPurchase;
use App\Models\VendingRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Shared LeadPurchase sale flags / filters for admin request lists.
 */
trait FiltersLeadSaleStatus
{
    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function withLeadSaleExists(Builder $query): Builder
    {
        return $query->withExists([
            'leadPurchases as is_sold' => fn (Builder $purchaseQuery) => $purchaseQuery
                ->where('status', LeadPurchase::STATUS_PAID),
            'leadPurchases as is_held' => fn (Builder $purchaseQuery) => $purchaseQuery
                ->whereNotNull('lead_lock'),
        ]);
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function applyLeadSaleFilter(Builder $query, ?string $sale): Builder
    {
        return match ($sale) {
            'sold' => $query->whereHas(
                'leadPurchases',
                fn (Builder $purchaseQuery) => $purchaseQuery->where('status', LeadPurchase::STATUS_PAID),
            ),
            'processing' => $query->whereHas(
                'leadPurchases',
                fn (Builder $purchaseQuery) => $purchaseQuery
                    ->whereNotNull('lead_lock')
                    ->where('status', LeadPurchase::STATUS_PENDING),
            ),
            'available' => $query->whereDoesntHave(
                'leadPurchases',
                fn (Builder $purchaseQuery) => $purchaseQuery->whereNotNull('lead_lock'),
            ),
            default => $query,
        };
    }

    /**
     * @param  LengthAwarePaginator<int, covariant Model>  $paginator
     * @return LengthAwarePaginator<int, covariant Model>
     */
    protected function castLeadSaleFlags(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $paginator->getCollection()->transform(function (Model $lead): Model {
            $lead->setAttribute('is_sold', (bool) $lead->getAttribute('is_sold'));
            $lead->setAttribute('is_held', (bool) $lead->getAttribute('is_held'));

            return $lead;
        });

        return $paginator;
    }

    /**
     * Load sale exists flags and the active buyer (paid or pending hold).
     *
     * @param  VendingRequest|CoffeeServiceRequest  $lead
     * @return array{name: string, email: string, purchase_status: string}|null
     */
    protected function loadLeadSaleForAdmin(Model $lead): ?array
    {
        $lead->loadExists([
            'leadPurchases as is_sold' => fn (Builder $purchaseQuery) => $purchaseQuery
                ->where('status', LeadPurchase::STATUS_PAID),
            'leadPurchases as is_held' => fn (Builder $purchaseQuery) => $purchaseQuery
                ->whereNotNull('lead_lock'),
        ]);

        $lead->setAttribute('is_sold', (bool) $lead->getAttribute('is_sold'));
        $lead->setAttribute('is_held', (bool) $lead->getAttribute('is_held'));

        /** @var MorphMany<LeadPurchase, Model> $purchases */
        $purchases = $lead->leadPurchases();

        $active = $purchases
            ->where(function (Builder $query): void {
                $query->where('status', LeadPurchase::STATUS_PAID)
                    ->orWhereNotNull('lead_lock');
            })
            ->with('user:id,name,email')
            ->latest('id')
            ->first();

        if ($active === null || $active->user === null) {
            return null;
        }

        return [
            'name' => $active->user->name,
            'email' => $active->user->email,
            'purchase_status' => $active->status,
        ];
    }
}
