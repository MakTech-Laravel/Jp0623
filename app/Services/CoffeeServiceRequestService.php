<?php

namespace App\Services;

use App\Jobs\NotifyNearbyOperatorsOfLead;
use App\Models\CoffeeServiceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CoffeeServiceRequestService
{
    /**
     * @return LengthAwarePaginator<int, CoffeeServiceRequest>
     */
    public function paginate(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 10,
    ): LengthAwarePaginator {
        $search = trim((string) $search);
        $status = filled($status) ? (string) $status : null;

        return CoffeeServiceRequest::query()
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
            ->when($status !== null, fn($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): CoffeeServiceRequest
    {
        $data['terms_accepted'] = (bool) ($data['terms_accepted'] ?? false);
        $data['status'] = CoffeeServiceRequest::STATUS_NEW;
        $data['notes'] = filled($data['notes'] ?? null) ? trim((string) $data['notes']) : null;
        $data = $this->normalizeLocation($data);

        $coffeeServiceRequest = CoffeeServiceRequest::create($data);

        NotifyNearbyOperatorsOfLead::dispatch('coffee', $coffeeServiceRequest->id);

        return $coffeeServiceRequest;
    }

    public function updateStatus(CoffeeServiceRequest $coffeeServiceRequest, string $status): CoffeeServiceRequest
    {
        $coffeeServiceRequest->update(['status' => $status]);

        return $coffeeServiceRequest->refresh();
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
