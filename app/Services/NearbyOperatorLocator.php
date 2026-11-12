<?php

namespace App\Services;

use App\Models\OperatorProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NearbyOperatorLocator
{
    private const EARTH_RADIUS_MILES = 3958.7613;

    private const MILES_PER_DEGREE_LATITUDE = 69.0;

    /**
     * Find operators with a phone number within the given radius.
     * Uses a bounding-box prefilter, then exact Haversine in PHP (SQLite/MySQL safe).
     *
     * @return Collection<int, OperatorProfile>
     */
    public function withinMiles(float $latitude, float $longitude, float $radiusMiles): Collection
    {
        return $this->betweenMiles($latitude, $longitude, 0, $radiusMiles);
    }

    /**
     * Find operators whose distance from a point falls in [minMiles, maxMiles].
     * Pass null for $maxMiles to apply only a minimum distance.
     *
     * @return Collection<int, OperatorProfile>
     */
    public function betweenMiles(
        float $latitude,
        float $longitude,
        float $minMiles = 0.0,
        ?float $maxMiles = null,
        ?string $search = null,
    ): Collection {
        $minMiles = max(0.0, $minMiles);
        $effectiveMax = $maxMiles ?? 5000.0;

        if ($effectiveMax <= 0 || $minMiles > $effectiveMax) {
            return collect();
        }

        $latDelta = $effectiveMax / self::MILES_PER_DEGREE_LATITUDE;
        $lngDenominator = max(abs(cos(deg2rad($latitude))) * self::MILES_PER_DEGREE_LATITUDE, 0.01);
        $lngDelta = $effectiveMax / $lngDenominator;

        return $this->baseQuery($search)
            ->whereBetween('latitude', [$latitude - $latDelta, $latitude + $latDelta])
            ->whereBetween('longitude', [$longitude - $lngDelta, $longitude + $lngDelta])
            ->orderBy('id')
            ->get()
            ->map(function (OperatorProfile $operator) use ($latitude, $longitude): OperatorProfile {
                $operator->setAttribute(
                    'distance_miles',
                    round($this->distanceMiles(
                        $latitude,
                        $longitude,
                        (float) $operator->latitude,
                        (float) $operator->longitude,
                    ), 1),
                );

                return $operator;
            })
            ->filter(function (OperatorProfile $operator) use ($minMiles, $maxMiles): bool {
                $distance = (float) $operator->getAttribute('distance_miles');

                if ($distance < $minMiles) {
                    return false;
                }

                if ($maxMiles !== null && $distance > $maxMiles) {
                    return false;
                }

                return true;
            })
            ->sortBy('distance_miles')
            ->values();
    }

    /**
     * @return Collection<int, OperatorProfile>
     */
    public function searchable(?string $search = null, int $limit = 500): Collection
    {
        return $this->baseQuery($search)
            ->orderBy('company_name')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->values();
    }

    public function distanceMiles(
        float $fromLatitude,
        float $fromLongitude,
        float $toLatitude,
        float $toLongitude,
    ): float {
        $latFrom = deg2rad($fromLatitude);
        $latTo = deg2rad($toLatitude);
        $latDelta = deg2rad($toLatitude - $fromLatitude);
        $lngDelta = deg2rad($toLongitude - $fromLongitude);

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;

        return 2 * self::EARTH_RADIUS_MILES * asin(min(1, sqrt($a)));
    }

    /**
     * @return Builder<OperatorProfile>
     */
    private function baseQuery(?string $search = null): Builder
    {
        $search = trim((string) $search);

        return OperatorProfile::query()
            ->with(['user:id,name,email'])
            ->select([
                'id',
                'user_id',
                'first_name',
                'last_name',
                'company_name',
                'phone',
                'latitude',
                'longitude',
                'city',
                'state',
                'primary_city',
            ])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('primary_city', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            });
    }
}
