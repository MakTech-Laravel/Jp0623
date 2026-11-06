<?php

namespace App\Services;

use App\Models\VendingRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VendingRequestService
{
    /**
     * @return LengthAwarePaginator<int, VendingRequest>
     */
    public function paginate(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 10,
    ): LengthAwarePaginator {
        $search = trim((string) $search);
        $status = filled($status) ? (string) $status : null;

        return VendingRequest::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('business_name', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address_or_zip', 'like', "%{$search}%");
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
    public function create(array $data): VendingRequest
    {
        $data['terms_accepted'] = (bool) ($data['terms_accepted'] ?? false);
        $data['status'] = VendingRequest::STATUS_NEW;
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

        return VendingRequest::create($data);
    }

    public function updateStatus(VendingRequest $vendingRequest, string $status): VendingRequest
    {
        $vendingRequest->update(['status' => $status]);

        return $vendingRequest->refresh();
    }

    public function delete(VendingRequest $vendingRequest): void
    {
        $vendingRequest->delete();
    }
}
