<?php

namespace App\Services;

use App\Enums\OperatorSmsStatus;
use App\Jobs\NotifyNearbyOperatorsOfLead;
use App\Models\LeadPurchase;
use App\Models\VendingRequest;
use App\Services\Concerns\FiltersLeadSaleStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class VendingRequestService
{
    use FiltersLeadSaleStatus;

    /**
     * @return LengthAwarePaginator<int, VendingRequest>
     */
    public function paginate(
        ?string $search = null,
        ?string $status = null,
        ?string $sale = null,
        int $perPage = 10,
    ): LengthAwarePaginator {
        $search = trim((string) $search);
        $status = filled($status) ? (string) $status : null;
        $sale = filled($sale) ? (string) $sale : null;

        $query = VendingRequest::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('business_name', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address_or_zip', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%")
                        ->orWhere('postal_code', 'like', "%{$search}%");
                });
            })
            ->when($status !== null, fn ($query) => $query->where('status', $status));

        $this->applyLeadSaleFilter($query, $sale);
        $this->withLeadSaleExists($query);

        return $this->castLeadSaleFlags(
            $query
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
        );
    }

    /**
     * @return array{name: string, email: string, purchase_status: string}|null
     */
    public function prepareForAdminShow(VendingRequest $vendingRequest): ?array
    {
        return $this->loadLeadSaleForAdmin($vendingRequest);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): VendingRequest
    {
        $data['terms_accepted'] = (bool) ($data['terms_accepted'] ?? false);
        $data['status'] = VendingRequest::STATUS_NEW;
        $data['operator_sms_status'] = OperatorSmsStatus::PENDING->value;
        $data['operator_sms_sent_count'] = 0;
        $data['operator_sms_failed_count'] = 0;
        $data['operator_sms_provider_status'] = null;
        $data['operator_sms_note'] = 'Waiting to notify nearby operators.';
        $data['operator_sms_notified_at'] = null;
        $data['machine_interests'] = array_values(array_filter(
            array_map(
                static fn (mixed $interest): string => trim((string) $interest),
                is_array($data['machine_interests'] ?? null) ? $data['machine_interests'] : [],
            ),
            static fn (string $interest): bool => $interest !== '',
        ));

        if (($data['existing_vending'] ?? '') === 'No') {
            $data['switch_reason'] = null;
        }

        $wasPricedOnCreate = array_key_exists('price', $data) && $data['price'] !== null && $data['price'] !== '';

        if ($wasPricedOnCreate) {
            $data['price'] = max(1, (int) $data['price']);
        } else {
            $data['price'] = null;
            $data['operator_sms_note'] = 'Waiting for an admin to set a price before notifying operators.';
        }

        $data = $this->normalizeLocation($data);

        $vendingRequest = VendingRequest::create($data);

        if ($wasPricedOnCreate) {
            NotifyNearbyOperatorsOfLead::dispatch('vending', $vendingRequest->id);
        }

        return $vendingRequest;
    }

    /**
     * @param  array{status?: string, price?: int}  $data
     */
    public function update(VendingRequest $vendingRequest, array $data): VendingRequest
    {
        $updates = [];
        $becamePriced = false;

        if (array_key_exists('status', $data)) {
            $updates['status'] = $data['status'];
        }

        if (array_key_exists('price', $data)) {
            if ($this->hasPaidPurchase($vendingRequest)) {
                throw ValidationException::withMessages([
                    'price' => 'Price cannot be changed after this lead has been sold.',
                ]);
            }

            $previousPrice = $vendingRequest->price;
            $updates['price'] = max(1, (int) $data['price']);
            $becamePriced = $previousPrice === null || (int) $previousPrice < 1;
        }

        if ($updates !== []) {
            $vendingRequest->update($updates);
        }

        if ($becamePriced) {
            NotifyNearbyOperatorsOfLead::dispatch('vending', $vendingRequest->id);
        }

        return $vendingRequest->refresh();
    }

    public function updateStatus(VendingRequest $vendingRequest, string $status): VendingRequest
    {
        return $this->update($vendingRequest, ['status' => $status]);
    }

    public function hasPaidPurchase(VendingRequest $vendingRequest): bool
    {
        return $vendingRequest->leadPurchases()
            ->where('status', LeadPurchase::STATUS_PAID)
            ->exists();
    }

    public function delete(VendingRequest $vendingRequest): void
    {
        $vendingRequest->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeLocation(array $data): array
    {
        $city = trim((string) ($data['city'] ?? ''));
        $state = trim((string) ($data['state'] ?? ''));
        $address = filled($data['address'] ?? null) ? trim((string) $data['address']) : null;
        $postalCode = filled($data['postal_code'] ?? null) ? trim((string) $data['postal_code']) : null;

        $data['city'] = $city;
        $data['state'] = $state;
        $data['address'] = $address;
        $data['postal_code'] = $postalCode;
        $data['latitude'] = $data['latitude'] ?? null;
        $data['longitude'] = $data['longitude'] ?? null;

        $label = filled($data['address_or_zip'] ?? null)
            ? trim((string) $data['address_or_zip'])
            : $this->composeLocationLabel($address, $city, $state, $postalCode);

        $data['address_or_zip'] = $label !== '' ? $label : "{$city}, {$state}";

        return $data;
    }

    private function composeLocationLabel(
        ?string $address,
        string $city,
        string $state,
        ?string $postalCode,
    ): string {
        $parts = array_values(array_filter([
            $address,
            $city,
            trim($state.($postalCode !== null ? ' '.$postalCode : '')),
        ], static fn (?string $part): bool => filled($part)));

        return implode(', ', $parts);
    }
}
