<?php

namespace App\Services;

use App\Models\MachineCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MachineCategoryService
{
    /**
     * @return LengthAwarePaginator<int, MachineCategory>
     */
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return MachineCategory::query()
            ->withCount('machines')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): MachineCategory
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? true;

        return MachineCategory::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(MachineCategory $category, array $data): MachineCategory
    {
        if (array_key_exists('slug', $data) || array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug(
                $data['slug'] ?? $data['name'] ?? $category->name,
                $category->id,
            );
        }

        $category->update($data);

        return $category->refresh();
    }

    public function delete(MachineCategory $category): void
    {
        if ($category->machines()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'This category has machines assigned. Reassign or delete them first.',
            ]);
        }

        $category->delete();
    }

    public function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $base = $base !== '' ? $base : 'category';
        $slug = $base;
        $suffix = 1;

        while (
            MachineCategory::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
