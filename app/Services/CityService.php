<?php

namespace App\Services;

use App\Imports\CitiesImport;
use App\Models\City;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;

class CityService
{
    /**
     * @return LengthAwarePaginator<int, City>
     */
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return City::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('city', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return list<array{city: string, state: string, label: string, href: string}>
     */
    public function listForFrontend(?int $limit = null): array
    {
        return City::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->when($limit !== null, fn ($query) => $query->limit($limit))
            ->get()
            ->map(fn (City $city): array => [
                'city' => $city->city,
                'state' => $city->state,
                'label' => $city->city.' vending',
                'href' => '/cities',
            ])
            ->values()
            ->all();
    }

    public function nextSortOrder(): int
    {
        return (int) City::query()->max('sort_order') + 1;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): City
    {
        $data['sort_order'] = array_key_exists('sort_order', $data) && $data['sort_order'] !== null
            ? (int) $data['sort_order']
            : $this->nextSortOrder();
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        return City::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(City $city, array $data): City
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $city->update($data);

        return $city->refresh();
    }

    public function delete(City $city): void
    {
        $city->delete();
    }

    /**
     * Upsert cities from an uploaded CSV / Excel file.
     *
     * @return array{created: int, updated: int, skipped: int, errors: list<array{row: int, message: string}>}
     */
    public function importFromUpload(UploadedFile $file): array
    {
        $import = new CitiesImport;

        $readerType = match (strtolower($file->getClientOriginalExtension())) {
            'xlsx' => ExcelType::XLSX,
            'xls' => ExcelType::XLS,
            default => ExcelType::CSV,
        };

        DB::transaction(fn () => Excel::import($import, $file, null, $readerType));

        return [
            'created' => $import->created,
            'updated' => $import->updated,
            'skipped' => $import->skipped,
            'errors' => $import->errors,
        ];
    }
}
