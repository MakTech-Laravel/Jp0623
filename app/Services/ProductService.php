<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * @return array{
     *     disclaimer: string,
     *     filters: list<string>,
     *     items: list<array{kind: string, name: string, desc: string, tags: list<string>, image: string|null}>
     * }
     */
    public function catalogForFrontend(): array
    {
        $items = Product::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Product $product): array => $this->toFrontendCatalogItem($product))
            ->values()
            ->all();

        return [
            'disclaimer' => 'Availability of products may vary by location',
            'filters' => array_values(array_merge(
                ['All'],
                array_column($items, 'name'),
            )),
            'items' => $items,
        ];
    }

    public function nextSortOrder(): int
    {
        return (int) Product::query()->max('sort_order') + 1;
    }

    /**
     * @return array{kind: string, name: string, desc: string, tags: list<string>, image: string|null}
     */
    public function toFrontendCatalogItem(Product $product): array
    {
        return [
            'kind' => $product->kind,
            'name' => $product->name,
            'desc' => $product->description ?? '',
            'tags' => $product->tags ?? [],
            'image' => $product->image_url,
        ];
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(
        ?string $search = null,
        int $perPage = 10,
    ): LengthAwarePaginator {
        $search = trim((string) $search);

        return Product::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('kind', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $image = null): Product
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);
        $data['kind'] = filled($data['kind'] ?? null)
            ? Str::slug((string) $data['kind'])
            : Str::slug((string) ($data['name'] ?? 'product'));
        $data['tags'] = $this->normalizeTags($data['tags'] ?? []);
        $data['sort_order'] = array_key_exists('sort_order', $data) && $data['sort_order'] !== null
            ? (int) $data['sort_order']
            : $this->nextSortOrder();
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        if ($image instanceof UploadedFile) {
            $data['image'] = $image->store('products', 'public');
        }

        unset($data['remove_image']);

        return Product::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data, ?UploadedFile $image = null): Product
    {
        if (array_key_exists('slug', $data) || array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug(
                $data['slug'] ?? $data['name'] ?? $product->name,
                $product->id,
            );
        }

        if (array_key_exists('kind', $data)) {
            $data['kind'] = filled($data['kind'])
                ? Str::slug((string) $data['kind'])
                : $product->kind;
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
            $this->deleteStoredImage($product);
            $data['image'] = $image->store('products', 'public');
        } elseif ($removeImage) {
            $this->deleteStoredImage($product);
            $data['image'] = null;
        }

        $product->update($data);

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $this->deleteStoredImage($product);
        $product->delete();
    }

    public function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $base = $base !== '' ? $base : 'product';
        $slug = $base;
        $suffix = 1;

        while (
            Product::query()
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

    private function deleteStoredImage(Product $product): void
    {
        if ($product->usesStoredUpload() && $product->image !== null) {
            Storage::disk('public')->delete($product->image);
        }
    }
}
