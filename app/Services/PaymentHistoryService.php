<?php

namespace App\Services;

use App\Models\CoffeeServiceRequest;
use App\Models\LeadPurchase;
use App\Models\MembershipPayment;
use App\Models\User;
use App\Models\VendingRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

/**
 * Builds operator billing invoices and the admin payments list from lead and membership payments.
 */
class PaymentHistoryService
{
    /**
     * Paid purchases for the operator's billing page (newest first).
     *
     * @return list<array{
     *     id: string,
     *     source: string,
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
        $leads = $user->leadPurchases()
            ->paid()
            ->with('leadable')
            ->get()
            ->filter(fn (LeadPurchase $purchase): bool => $purchase->leadable !== null)
            ->map(fn (LeadPurchase $purchase): array => $this->mapInvoiceFromLead($purchase));

        $memberships = $user->membershipPayments()
            ->paid()
            ->get()
            ->map(fn (MembershipPayment $payment): array => $this->mapInvoiceFromMembership($payment));

        return $leads
            ->concat($memberships)
            ->sortByDesc(fn (array $row): string => $row['sort_at'])
            ->values()
            ->map(function (array $row): array {
                unset($row['sort_at']);

                return $row;
            })
            ->all();
    }

    /**
     * Latest paid card on file for display only.
     *
     * @return array{brand: string, last4: string}|null
     */
    public function latestPaidCardFor(User $user): ?array
    {
        $leadCard = $user->leadPurchases()
            ->paid()
            ->whereNotNull('card_last4')
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->first();

        $membershipCard = $user->membershipPayments()
            ->paid()
            ->whereNotNull('card_last4')
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->first();

        $candidates = Collection::make([$leadCard, $membershipCard])
            ->filter()
            ->sortByDesc(fn ($row) => $row->paid_at?->timestamp ?? 0)
            ->values();

        $latest = $candidates->first();

        if ($latest === null || $latest->card_last4 === null) {
            return null;
        }

        return [
            'brand' => (string) ($latest->card_brand ?: 'Card'),
            'last4' => (string) $latest->card_last4,
        ];
    }

    /**
     * Admin payments index (lead + membership).
     *
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateForAdmin(
        ?string $search = null,
        ?string $status = null,
        ?string $source = null,
        int $perPage = 10,
    ): LengthAwarePaginator {
        $search = trim((string) $search);
        $status = filled($status) ? (string) $status : null;
        $source = filled($source) ? (string) $source : null;
        $page = max(1, (int) request()->integer('page', 1));

        $rows = Collection::make();

        if ($source === null || $source === 'lead') {
            $rows = $rows->concat($this->adminLeadRows($search, $status));
        }

        if ($source === null || $source === 'membership') {
            $rows = $rows->concat($this->adminMembershipRows($search, $status));
        }

        $sorted = $rows
            ->sortByDesc(fn (array $row): string => $row['sort_at'])
            ->values();

        $total = $sorted->count();
        $items = $sorted
            ->slice(($page - 1) * $perPage, $perPage)
            ->map(function (array $row): array {
                unset($row['sort_at']);

                return $row;
            })
            ->values();

        return new Paginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
    }

    /**
     * @return array{lead_mtd: float, lead_all: float, membership_mtd: float, membership_all: float, total_mtd: float}
     */
    public function revenueSummary(): array
    {
        $monthStart = now()->startOfMonth();

        $leadAllCents = (int) LeadPurchase::query()->paid()->sum('amount_cents');
        $leadMtdCents = (int) LeadPurchase::query()
            ->paid()
            ->where('paid_at', '>=', $monthStart)
            ->sum('amount_cents');

        $membershipAllCents = (int) MembershipPayment::query()->paid()->sum('amount_cents');
        $membershipMtdCents = (int) MembershipPayment::query()
            ->paid()
            ->where('paid_at', '>=', $monthStart)
            ->sum('amount_cents');

        return [
            'lead_mtd' => round($leadMtdCents / 100, 2),
            'lead_all' => round($leadAllCents / 100, 2),
            'membership_mtd' => round($membershipMtdCents / 100, 2),
            'membership_all' => round($membershipAllCents / 100, 2),
            'total_mtd' => round(($leadMtdCents + $membershipMtdCents) / 100, 2),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function adminLeadRows(?string $search, ?string $status): Collection
    {
        return LeadPurchase::query()
            ->with(['user', 'leadable'])
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $this->applyLeadSearch($query, $search);
            })
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (LeadPurchase $purchase): array => $this->mapAdminFromLead($purchase));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function adminMembershipRows(?string $search, ?string $status): Collection
    {
        return MembershipPayment::query()
            ->with('user')
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $this->applyMembershipSearch($query, $search);
            })
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (MembershipPayment $payment): array => $this->mapAdminFromMembership($payment));
    }

    /**
     * @return array<string, mixed>
     */
    private function mapInvoiceFromLead(LeadPurchase $purchase): array
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
        $paidAt = $purchase->paid_at ?? $purchase->purchased_at ?? $purchase->created_at;

        return [
            'id' => 'LP-'.$purchase->id,
            'source' => 'lead',
            'location' => filled($location) ? $location : null,
            'date' => $paidAt->format('M j, Y'),
            'status' => 'Paid',
            'description' => 'Location lead ('.$source.')',
            'amount' => round($amountCents / 100, 2),
            'qty' => 1,
            'receipt_url' => $purchase->square_receipt_url,
            'card_brand' => $purchase->card_brand,
            'card_last4' => $purchase->card_last4,
            'sort_at' => $paidAt->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapInvoiceFromMembership(MembershipPayment $payment): array
    {
        $paidAt = $payment->paid_at ?? $payment->created_at;

        return [
            'id' => 'MB-'.$payment->id,
            'source' => 'membership',
            'location' => null,
            'date' => $paidAt->format('M j, Y'),
            'status' => 'Paid',
            'description' => 'Monthly membership',
            'amount' => round(($payment->amount_cents ?: ((int) $payment->price * 100)) / 100, 2),
            'qty' => 1,
            'receipt_url' => $payment->square_receipt_url,
            'card_brand' => $payment->card_brand,
            'card_last4' => $payment->card_last4,
            'sort_at' => $paidAt->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapAdminFromLead(LeadPurchase $purchase): array
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
        $paidAt = $purchase->paid_at;

        return [
            'id' => 'lead-'.$purchase->id,
            'invoice_id' => 'LP-'.$purchase->id,
            'source' => 'lead',
            'source_label' => 'Lead purchase',
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
            'paid_at' => $paidAt?->toIso8601String(),
            'paid_at_label' => $paidAt?->format('M j, Y g:i A'),
            'created_at' => $purchase->created_at?->toIso8601String(),
            'sort_at' => ($paidAt ?? $purchase->created_at)->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapAdminFromMembership(MembershipPayment $payment): array
    {
        $paidAt = $payment->paid_at;

        return [
            'id' => 'membership-'.$payment->id,
            'invoice_id' => 'MB-'.$payment->id,
            'source' => 'membership',
            'source_label' => 'Membership',
            'operator_name' => $payment->user?->name,
            'operator_email' => $payment->user?->email,
            'lead_public_id' => '—',
            'lead_name' => 'Monthly membership',
            'lead_source' => 'membership',
            'amount' => round(($payment->amount_cents ?: ((int) $payment->price * 100)) / 100, 2),
            'currency' => $payment->currency,
            'status' => $payment->status,
            'payment_provider' => $payment->payment_provider,
            'card_brand' => $payment->card_brand,
            'card_last4' => $payment->card_last4,
            'square_payment_id' => $payment->square_payment_id,
            'receipt_url' => $payment->square_receipt_url,
            'paid_at' => $paidAt?->toIso8601String(),
            'paid_at_label' => $paidAt?->format('M j, Y g:i A'),
            'created_at' => $payment->created_at?->toIso8601String(),
            'sort_at' => ($paidAt ?? $payment->created_at)->toIso8601String(),
        ];
    }

    private function applyLeadSearch($query, string $search): void
    {
        $normalizedId = null;

        if (preg_match('/^LP-(\d+)$/i', $search, $matches) === 1) {
            $normalizedId = (int) $matches[1];
        } elseif (ctype_digit($search)) {
            $normalizedId = (int) $search;
        }

        $query->where(function ($query) use ($search, $normalizedId): void {
            $query->where('square_payment_id', 'like', "%{$search}%")
                ->orWhere('square_receipt_number', 'like', "%{$search}%")
                ->orWhereHas('user', function ($userQuery) use ($search): void {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });

            if ($normalizedId !== null) {
                $query->orWhere('id', $normalizedId);
            }
        });
    }

    private function applyMembershipSearch($query, string $search): void
    {
        $normalizedId = null;

        if (preg_match('/^MB-(\d+)$/i', $search, $matches) === 1) {
            $normalizedId = (int) $matches[1];
        } elseif (ctype_digit($search)) {
            $normalizedId = (int) $search;
        }

        $query->where(function ($query) use ($search, $normalizedId): void {
            $query->where('square_payment_id', 'like', "%{$search}%")
                ->orWhere('square_receipt_number', 'like', "%{$search}%")
                ->orWhereHas('user', function ($userQuery) use ($search): void {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });

            if ($normalizedId !== null) {
                $query->orWhere('id', $normalizedId);
            }
        });
    }
}
