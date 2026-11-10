<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PhotonLocationService
{
    /**
     * @return list<array{
     *     label: string,
     *     address: string|null,
     *     city: string,
     *     state: string,
     *     postal_code: string|null,
     *     latitude: float,
     *     longitude: float
     * }>
     */
    public function suggest(string $query, int $limit = 8): array
    {
        $query = trim($query);

        if (Str::length($query) < 3) {
            return [];
        }

        $url = (string) config('services.photon.url');

        if ($url === '') {
            throw new RuntimeException('Photon API URL is not configured.');
        }

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->get($url, [
                    'q' => $query,
                    'limit' => max(1, min($limit, 15)),
                    'lang' => 'en',
                    'countrycode' => 'us',
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Unable to reach the location service.', 0, $exception);
        }

        if (! $response->successful()) {
            throw new RuntimeException('Location suggestions are temporarily unavailable.');
        }

        /** @var list<array<string, mixed>> $features */
        $features = $response->json('features') ?? [];

        $suggestions = [];

        foreach ($features as $feature) {
            $mapped = $this->mapFeature($feature);

            if ($mapped !== null) {
                $suggestions[] = $mapped;
            }
        }

        return array_values($suggestions);
    }

    /**
     * @param  array<string, mixed>  $feature
     * @return array{
     *     label: string,
     *     address: string|null,
     *     city: string,
     *     state: string,
     *     postal_code: string|null,
     *     latitude: float,
     *     longitude: float
     * }|null
     */
    private function mapFeature(array $feature): ?array
    {
        $properties = is_array($feature['properties'] ?? null)
            ? $feature['properties']
            : [];
        $geometry = is_array($feature['geometry'] ?? null)
            ? $feature['geometry']
            : [];
        $coordinates = is_array($geometry['coordinates'] ?? null)
            ? $geometry['coordinates']
            : [];

        if (count($coordinates) < 2) {
            return null;
        }

        $longitude = (float) $coordinates[0];
        $latitude = (float) $coordinates[1];

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        $city = $this->firstFilled($properties, [
            'city',
            'town',
            'village',
            'municipality',
            'county',
        ]);
        $state = $this->firstFilled($properties, [
            'state',
            'statecode',
        ]);

        if ($city === null || $state === null) {
            return null;
        }

        $address = $this->buildAddress($properties);
        $postalCode = $this->stringOrNull($properties['postcode'] ?? null);
        $label = $this->buildLabel($address, $city, $state, $postalCode);

        return [
            'label' => $label,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'postal_code' => $postalCode,
            'latitude' => round($latitude, 7),
            'longitude' => round($longitude, 7),
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     * @param  list<string>  $keys
     */
    private function firstFilled(array $properties, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->stringOrNull($properties[$key] ?? null);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function buildAddress(array $properties): ?string
    {
        $houseNumber = $this->stringOrNull($properties['housenumber'] ?? null);
        $street = $this->stringOrNull($properties['street'] ?? null);
        $name = $this->stringOrNull($properties['name'] ?? null);

        if ($street !== null) {
            return trim(($houseNumber !== null ? $houseNumber.' ' : '').$street);
        }

        if ($name !== null && ! in_array($properties['type'] ?? null, ['city', 'town', 'village', 'state', 'county'], true)) {
            return $name;
        }

        return null;
    }

    private function buildLabel(
        ?string $address,
        string $city,
        string $state,
        ?string $postalCode,
    ): string {
        $parts = array_values(array_filter([
            $address,
            $city,
            $state.($postalCode !== null ? ' '.$postalCode : ''),
        ], static fn (?string $part): bool => filled($part)));

        return implode(', ', $parts);
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
