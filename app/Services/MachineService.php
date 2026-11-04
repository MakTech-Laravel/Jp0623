<?php

namespace App\Services;

use App\Models\Machine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MachineService
{
    /**
     * @return LengthAwarePaginator<int, Machine>
     */
    public function paginate(
        ?string $search = null,
        ?int $categoryId = null,
        int $perPage = 10,
    ): LengthAwarePaginator {
        $search = trim((string) $search);

        return Machine::query()
            ->with('category:id,name,slug')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('kind', 'like', "%{$search}%");
                });
            })
            ->when($categoryId !== null, fn ($query) => $query->where('machine_category_id', $categoryId))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $image = null): Machine
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);
        $data['tags'] = $this->normalizeTags($data['tags'] ?? []);
        $data['status'] = $data['status'] ?? 'Available nationwide';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        if ($image instanceof UploadedFile) {
            $data['image'] = $image->store('machines', 'public');
        }

        unset($data['remove_image']);

        return Machine::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Machine $machine, array $data, ?UploadedFile $image = null): Machine
    {
        if (array_key_exists('slug', $data) || array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug(
                $data['slug'] ?? $data['name'] ?? $machine->name,
                $machine->id,
            );
        }

        if (array_key_exists('tags', $data)) {
            $data['tags'] = $this->normalizeTags($data['tags']);
        }

        if (array_key_exists('is_featured', $data)) {
            $data['is_featured'] = (bool) $data['is_featured'];
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $removeImage = (bool) ($data['remove_image'] ?? false);
        unset($data['remove_image']);

        if ($image instanceof UploadedFile) {
            $this->deleteStoredImage($machine);
            $data['image'] = $image->store('machines', 'public');
        } elseif ($removeImage) {
            $this->deleteStoredImage($machine);
            $data['image'] = null;
        }

        $machine->update($data);

        return $machine->refresh();
    }

    public function delete(Machine $machine): void
    {
        $this->deleteStoredImage($machine);
        $machine->delete();
    }

    public function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $base = $base !== '' ? $base : 'machine';
        $slug = $base;
        $suffix = 1;

        while (
            Machine::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return list<string>
     */
    public function normalizeTags(mixed $tags): array
    {
        if (is_string($tags)) {
            $tags = preg_split('/\s*,\s*/', $tags) ?: [];
        }

        if (! is_array($tags)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $tag): string => trim((string) $tag),
            $tags,
        ), static fn (string $tag): bool => $tag !== ''));
    }

    private function deleteStoredImage(Machine $machine): void
    {
        if ($machine->usesStoredUpload() && $machine->image !== null) {
            Storage::disk('public')->delete($machine->image);
        }
    }
}
