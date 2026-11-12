<?php

namespace App\Services;

use App\Models\LocationType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LocationTypeService
{
    /**
     * @return LengthAwarePaginator<int, LocationType>
     */
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return LocationType::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return list<string>
     */
    public function listActiveNamesForFrontend(): array
    {
        return LocationType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('name')
            ->values()
            ->all();
    }

    public function nextSortOrder(): int
    {
        return (int) LocationType::query()->max('sort_order') + 1;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): LocationType
    {
        $data['sort_order'] = array_key_exists('sort_order', $data) && $data['sort_order'] !== null
            ? (int) $data['sort_order']
            : $this->nextSortOrder();
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        return LocationType::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(LocationType $locationType, array $data): LocationType
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $locationType->update($data);

        return $locationType->refresh();
    }

    public function delete(LocationType $locationType): void
    {
        $locationType->delete();
    }
}
