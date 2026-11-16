<?php

namespace App\Imports;

use App\Models\City;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

/**
 * Bulk upload of service cities (CSV / Excel).
 *
 * Expected heading row: city, state, sort_order (optional), is_active (optional),
 * latitude (optional), longitude (optional; both are needed to be saved).
 * Rows are upserted on (city, state). Invalid rows are skipped and reported
 * with their spreadsheet row number; valid rows are still imported.
 */
class CitiesImport implements OnEachRow, WithHeadingRow
{
    public const MAX_ROWS = 5000;

    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    /** @var list<array{row: int, message: string}> */
    public array $errors = [];

    private int $processed = 0;

    private int $nextSortOrder;

    public function __construct()
    {
        $this->nextSortOrder = (int) City::query()->max('sort_order');
    }

    public function onRow(Row $row): void
    {
        if ($row->isEmpty()) {
            return;
        }

        $index = $row->getIndex();
        $this->processed++;

        if ($this->processed > self::MAX_ROWS) {
            if ($this->processed === self::MAX_ROWS + 1) {
                $this->errors[] = [
                    'row' => $index,
                    'message' => 'Row limit of '.self::MAX_ROWS.' reached; the remaining rows were ignored.',
                ];
            }
            $this->skipped++;

            return;
        }

        $data = $row->toArray();
        $city = trim((string) ($data['city'] ?? ''));
        $state = strtoupper(trim((string) ($data['state'] ?? '')));
        $sortOrder = $data['sort_order'] ?? null;
        $sortOrder = ($sortOrder === null || trim((string) $sortOrder) === '') ? null : trim((string) $sortOrder);
        $isActive = $this->parseActive($data['is_active'] ?? null);
        $latitude = $this->blankToNull($data['latitude'] ?? null);
        $longitude = $this->blankToNull($data['longitude'] ?? null);

        $validator = Validator::make(
            [
                'city' => $city,
                'state' => $state,
                'sort_order' => $sortOrder,
                'is_active' => $isActive,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            [
                'city' => ['required', 'string', 'max:255'],
                'state' => ['required', 'string', 'size:2', 'alpha'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
                'is_active' => ['nullable', 'boolean'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            ],
            [],
            [
                'city' => 'city',
                'state' => 'state',
                'sort_order' => 'sort_order',
                'is_active' => 'is_active',
                'latitude' => 'latitude',
                'longitude' => 'longitude',
            ],
        );

        if ($validator->fails()) {
            $this->skipped++;
            $this->errors[] = ['row' => $index, 'message' => $validator->errors()->first()];

            return;
        }

        $existing = City::query()
            ->where('city', $city)
            ->where('state', $state)
            ->first();

        $attributes = ['is_active' => $isActive ?? true];

        if ($latitude !== null && $longitude !== null) {
            $attributes['latitude'] = (float) $latitude;
            $attributes['longitude'] = (float) $longitude;
        }

        if ($sortOrder !== null) {
            $attributes['sort_order'] = (int) $sortOrder;
        } elseif ($existing === null) {
            $attributes['sort_order'] = ++$this->nextSortOrder;
        }

        if ($existing !== null) {
            $existing->update($attributes);
            $this->updated++;

            return;
        }

        City::create(['city' => $city, 'state' => $state] + $attributes);
        $this->created++;
    }

    private function blankToNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Blank means active. Unknown values return a string so validation rejects the row.
     */
    private function parseActive(mixed $value): bool|string|null
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return match (true) {
            in_array($normalized, ['1', 'true', 'yes', 'y', 'active'], true) => true,
            in_array($normalized, ['0', 'false', 'no', 'n', 'inactive'], true) => false,
            default => $normalized,
        };
    }
}
