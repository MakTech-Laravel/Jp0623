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
            ->orderBy('name')
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
        $data['sort_order'] = $data['sort_order'] ?? 0;
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
