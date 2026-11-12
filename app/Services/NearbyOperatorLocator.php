<?php

namespace App\Services;

use App\Models\OperatorProfile;
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
        if ($radiusMiles <= 0) {
            return collect();
        }

        $latDelta = $radiusMiles / self::MILES_PER_DEGREE_LATITUDE;
        $lngDenominator = max(abs(cos(deg2rad($latitude))) * self::MILES_PER_DEGREE_LATITUDE, 0.01);
        $lngDelta = $radiusMiles / $lngDenominator;

        return OperatorProfile::query()
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
            ])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereBetween('latitude', [$latitude - $latDelta, $latitude + $latDelta])
            ->whereBetween('longitude', [$longitude - $lngDelta, $longitude + $lngDelta])
            ->orderBy('id')
            ->get()
            ->filter(function (OperatorProfile $operator) use ($latitude, $longitude, $radiusMiles): bool {
                return $this->distanceMiles(
                    $latitude,
                    $longitude,
                    (float) $operator->latitude,
                    (float) $operator->longitude,
                ) <= $radiusMiles;
            })
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
}
