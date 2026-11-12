<?php

namespace App\Services;

use App\Jobs\SendAdminOperatorSms;
use App\Models\CoffeeServiceRequest;
use App\Models\OperatorProfile;
use App\Models\VendingRequest;
use Illuminate\Validation\ValidationException;

class AdminOperatorSmsService
{
    public function __construct(private NearbyOperatorLocator $locator) {}

    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     type: string,
     *     id: int,
     *     latitude: float,
     *     longitude: float,
     *     business_name: string,
     *     location: string
     * }>
     */
    public function referenceOptions(int $limit = 40): array
    {
        $vending = VendingRequest::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest('id')
            ->limit($limit)
            ->get(['id', 'business_name', 'city', 'state', 'address_or_zip', 'latitude', 'longitude']);

        $coffee = CoffeeServiceRequest::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest('id')
            ->limit($limit)
            ->get(['id', 'business_name', 'city', 'state', 'zip_code', 'latitude', 'longitude']);

        $options = [];

        foreach ($vending as $request) {
            $options[] = [
                'key' => 'vending:' . $request->id,
                'label' => 'Vending #' . $request->id . ' — ' . $request->business_name,
                'type' => 'vending',
                'id' => $request->id,
                'latitude' => (float) $request->latitude,
                'longitude' => (float) $request->longitude,
                'business_name' => $request->business_name,
                'location' => $this->formatLocation(
                    $request->city,
                    $request->state,
                    $request->address_or_zip,
                ),
            ];
        }

        foreach ($coffee as $request) {
            $options[] = [
                'key' => 'coffee:' . $request->id,
                'label' => 'Coffee #' . $request->id . ' — ' . $request->business_name,
                'type' => 'coffee',
                'id' => $request->id,
                'latitude' => (float) $request->latitude,
                'longitude' => (float) $request->longitude,
                'business_name' => $request->business_name,
                'location' => $this->formatLocation(
                    $request->city,
                    $request->state,
                    $request->zip_code,
                ),
            ];
        }

        return $options;
    }

    /**
     * @return array{
     *     operators: list<array<string, mixed>>,
     *     total: int,
     *     truncated: bool
     * }
     */
    public function listOperators(
        ?float $latitude,
        ?float $longitude,
        float $minMiles,
        ?float $maxMiles,
        ?string $search,
        int $limit = 500,
    ): array {
        if ($latitude !== null && $longitude !== null) {
            $operators = $this->locator->betweenMiles(
                $latitude,
                $longitude,
                $minMiles,
                $maxMiles,
                $search,
            );
        } else {
            $operators = $this->locator->searchable($search, $limit);
        }

        $total = $operators->count();
        $truncated = $total > $limit;
        $operators = $operators->take($limit)->values();

        return [
            'operators' => $operators->map(fn(OperatorProfile $operator): array => $this->serialize($operator))->all(),
            'total' => $total,
            'truncated' => $truncated,
        ];
    }

    /**
     * @param  list<int>  $operatorProfileIds
     */
    public function queueSend(array $operatorProfileIds, string $message): int
    {
        $message = trim($message);

        if ($message === '') {
            throw ValidationException::withMessages([
                'message' => 'Message is required.',
            ]);
        }

        $ids = OperatorProfile::query()
            ->whereIn('id', $operatorProfileIds)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn($id): int => (int) $id)
            ->all();

        if ($ids === []) {
            throw ValidationException::withMessages([
                'operator_ids' => 'Select at least one operator with a phone number.',
            ]);
        }

        SendAdminOperatorSms::dispatch($ids, $message);

        return count($ids);
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(OperatorProfile $operator): array
    {
        return [
            'id' => $operator->id,
            'user_id' => $operator->user_id,
            'name' => trim($operator->first_name . ' ' . $operator->last_name),
            'email' => $operator->user?->email,
            'company_name' => $operator->company_name,
            'phone' => $operator->phone,
            'city' => $operator->city,
            'state' => $operator->state,
            'primary_city' => $operator->primary_city,
            'latitude' => $operator->latitude !== null ? (float) $operator->latitude : null,
            'longitude' => $operator->longitude !== null ? (float) $operator->longitude : null,
            'distance_miles' => $operator->getAttribute('distance_miles'),
        ];
    }

    private function formatLocation(?string $city, ?string $state, ?string $fallback = null): string
    {
        $parts = array_values(array_filter([
            filled($city) ? trim((string) $city) : null,
            filled($state) ? trim((string) $state) : null,
        ]));

        if ($parts !== []) {
            return implode(', ', $parts);
        }

        return filled($fallback) ? trim((string) $fallback) : '';
    }
}
