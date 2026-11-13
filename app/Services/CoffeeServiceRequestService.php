<?php

namespace App\Services;

use App\Enums\OperatorSmsStatus;
use App\Jobs\NotifyNearbyOperatorsOfLead;
use App\Models\CoffeeServiceRequest;
use App\Models\LeadPurchase;
use App\Services\Concerns\FiltersLeadSaleStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class CoffeeServiceRequestService
{
    use FiltersLeadSaleStatus;

    /**
     * @return LengthAwarePaginator<int, CoffeeServiceRequest>
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

        $query = CoffeeServiceRequest::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('business_name', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('zip_code', 'like', "%{$search}%")
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
    public function prepareForAdminShow(CoffeeServiceRequest $coffeeServiceRequest): ?array
    {
        return $this->loadLeadSaleForAdmin($coffeeServiceRequest);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): CoffeeServiceRequest
    {
        $data['terms_accepted'] = (bool) ($data['terms_accepted'] ?? false);
        $data['status'] = CoffeeServiceRequest::STATUS_NEW;
        $data['operator_sms_status'] = OperatorSmsStatus::PENDING->value;
        $data['operator_sms_sent_count'] = 0;
        $data['operator_sms_failed_count'] = 0;
        $data['operator_sms_provider_status'] = null;
        $data['operator_sms_note'] = 'Waiting to notify nearby operators.';
        $data['operator_sms_notified_at'] = null;
        $data['notes'] = filled($data['notes'] ?? null) ? trim((string) $data['notes']) : null;
        $data['price'] = max(1, (int) ($data['price'] ?? config('leads.price')));
        $data = $this->normalizeLocation($data);

        $coffeeServiceRequest = CoffeeServiceRequest::create($data);

        NotifyNearbyOperatorsOfLead::dispatch('coffee', $coffeeServiceRequest->id);

        return $coffeeServiceRequest;
    }

    /**
     * @param  array{status?: string, price?: int}  $data
     */
    public function update(CoffeeServiceRequest $coffeeServiceRequest, array $data): CoffeeServiceRequest
    {
        $updates = [];

        if (array_key_exists('status', $data)) {
            $updates['status'] = $data['status'];
        }

        if (array_key_exists('price', $data)) {
            if ($this->hasPaidPurchase($coffeeServiceRequest)) {
                throw ValidationException::withMessages([
                    'price' => 'Price cannot be changed after this lead has been sold.',
                ]);
            }

            $updates['price'] = max(1, (int) $data['price']);
        }

        if ($updates !== []) {
            $coffeeServiceRequest->update($updates);
        }

        return $coffeeServiceRequest->refresh();
    }

    public function updateStatus(CoffeeServiceRequest $coffeeServiceRequest, string $status): CoffeeServiceRequest
    {
        return $this->update($coffeeServiceRequest, ['status' => $status]);
    }

    public function hasPaidPurchase(CoffeeServiceRequest $coffeeServiceRequest): bool
    {
        return $coffeeServiceRequest->leadPurchases()
            ->where('status', LeadPurchase::STATUS_PAID)
            ->exists();
    }

    public function delete(CoffeeServiceRequest $coffeeServiceRequest): void
    {
        $coffeeServiceRequest->delete();
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

        if (filled($data['zip_code'] ?? null)) {
            $data['zip_code'] = trim((string) $data['zip_code']);
        } elseif ($postalCode !== null) {
            $data['zip_code'] = $postalCode;
        } else {
            $data['zip_code'] = trim("{$city}, {$state}");
        }

        return $data;
    }
}
