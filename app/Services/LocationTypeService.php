<?php

namespace App\Services;

use App\Models\LocationType;
use App\Models\Machine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
     * Every active location type name: the request form dropdowns (public and admin).
     *
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

    /**
     * Active location types that are shown as industries (home, /industries, machines page).
     *
     * @return list<LocationType>
     */
    public function listIndustries(): array
    {
        return LocationType::query()
            ->where('is_active', true)
            ->where('show_on_industries', true)
            ->whereNotNull('slug')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->all();
    }

    public function findIndustryBySlug(string $slug): ?LocationType
    {
        return LocationType::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where('show_on_industries', true)
            ->first();
    }

    public function nextSortOrder(): int
    {
        return (int) LocationType::query()->max('sort_order') + 1;
    }

    /**
     * Distinct machine kinds, offered as options for a location type's "kind".
     *
     * @return list<string>
     */
    public function machineKindOptions(): array
    {
        return Machine::query()
            ->distinct()
            ->orderBy('kind')
            ->pluck('kind')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $image = null): LocationType
    {
        $data['slug'] = $this->uniqueSlug(($data['slug'] ?? null) ?: $data['name']);
        $data['sort_order'] = array_key_exists('sort_order', $data) && $data['sort_order'] !== null
            ? (int) $data['sort_order']
            : $this->nextSortOrder();
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;
        $data['show_on_industries'] = array_key_exists('show_on_industries', $data)
            ? (bool) $data['show_on_industries']
            : true;

        if ($image instanceof UploadedFile) {
            $data['image'] = $image->store('location-types', 'public');
        }

        unset($data['remove_image']);

        return LocationType::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(LocationType $locationType, array $data, ?UploadedFile $image = null): LocationType
    {
        if (array_key_exists('slug', $data) || array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug(
                ($data['slug'] ?? null) ?: ($locationType->slug ?: ($data['name'] ?? $locationType->name)),
                $locationType->id,
            );
        }

        foreach (['is_active', 'show_on_industries'] as $flag) {
            if (array_key_exists($flag, $data)) {
                $data[$flag] = (bool) $data[$flag];
            }
        }

        $removeImage = (bool) ($data['remove_image'] ?? false);
        unset($data['remove_image']);

        if ($image instanceof UploadedFile) {
            $this->deleteStoredImage($locationType);
            $data['image'] = $image->store('location-types', 'public');
        } elseif ($removeImage) {
            $this->deleteStoredImage($locationType);
            $data['image'] = null;
        }

        $locationType->update($data);

        return $locationType->refresh();
    }

    public function delete(LocationType $locationType): void
    {
        $this->deleteStoredImage($locationType);
        $locationType->delete();
    }

    public function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $base = $base !== '' ? $base : 'location-type';
        $slug = $base;
        $suffix = 1;

        while (
            LocationType::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function deleteStoredImage(LocationType $locationType): void
    {
        if ($locationType->usesStoredUpload() && $locationType->image !== null) {
            Storage::disk('public')->delete($locationType->image);
        }
    }
}
