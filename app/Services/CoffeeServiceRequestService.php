<?php

namespace App\Services;

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
                        ->orWhere('zip_code', 'like', "%{$search}%");
                });
            })
            ->when($status !== null, fn ($query) => $query->where('status', $status))
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

        return CoffeeServiceRequest::create($data);
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
}
