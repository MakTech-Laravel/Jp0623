<?php

namespace App\Services;

use App\Models\Machine;
use App\Models\MachineCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MachineService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listForFrontend(): array
    {
        return Machine::query()
            ->with('category:id,name,slug')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Machine $machine): array => $this->toFrontendItem($machine))
            ->values()
            ->all();
    }

    public function nextSortOrder(): int
    {
        return (int) Machine::query()->max('sort_order') + 1;
    }

    /**
     * @return list<string>
     */
    public function lineupFilters(): array
    {
        $names = MachineCategory::query()
            ->where('is_active', true)
            ->whereHas('machines', fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        if ($names === []) {
            $names = MachineCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->all();
        }

        return array_values(array_merge(['All'], $names));
    }

    public function findActiveBySlug(string $slug): ?Machine
    {
        return Machine::query()
            ->with('category:id,name,slug')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function toFrontendItem(Machine $machine): array
    {
        $machine->loadMissing('category:id,name,slug');

        return [
            'id' => $machine->slug,
            'name' => $machine->name,
            'kind' => $machine->kind,
            'type' => $machine->category?->name ?? '',
            'tags' => $machine->tags ?? [],
            'desc' => $machine->description ?? '',
            'href' => '/machines/'.$machine->slug,
            'image' => $machine->image_url,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detailForFrontend(string $slug): ?array
    {
        $machine = $this->findActiveBySlug($slug);

        if ($machine === null) {
            return null;
        }

        $relatedItems = Machine::query()
            ->with('category:id,name,slug')
            ->where('is_active', true)
            ->where('id', '!=', $machine->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(3)
            ->get()
            ->map(fn (Machine $related): array => $this->toFrontendItem($related))
            ->values()
            ->all();

        return [
            'machine' => $this->toFrontendItem($machine),
            'status' => $machine->status,
            'specs' => $this->frontendSpecs($machine),
            'related' => [
                'title' => 'Other machines you might like.',
                'items' => $relatedItems,
            ],
        ];
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function frontendSpecs(Machine $machine): array
    {
        $specs = [];

        if (filled($machine->capacity)) {
            $specs[] = ['label' => 'Capacity', 'value' => $machine->capacity];
        }

        $specs[] = [
            'label' => 'Payments',
            'value' => 'Card, tap, Apple Pay, Google Pay, cash (optional)',
        ];
        $specs[] = [
            'label' => 'Power',
            'value' => '115V dedicated outlet',
        ];

        if (filled($machine->dimensions)) {
            $specs[] = ['label' => 'Dimensions', 'value' => $machine->dimensions];
        }

        if (filled($machine->best_for)) {
            $specs[] = ['label' => 'Best for', 'value' => $machine->best_for];
        }

        $specs[] = [
            'label' => 'Cost to you',
            'value' => '$0 — install, stocking and service included',
        ];

        return $specs;
    }

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
            ->orderBy('id')
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
        $data['sort_order'] = array_key_exists('sort_order', $data) && $data['sort_order'] !== null
            ? (int) $data['sort_order']
            : $this->nextSortOrder();
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
