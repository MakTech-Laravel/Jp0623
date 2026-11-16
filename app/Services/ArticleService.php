<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleService
{
    /**
     * @return LengthAwarePaginator<int, Article>
     */
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return Article::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Active articles for the public cards (home, /resources, /schools).
     *
     * @return list<array{id: string, cat: string, title: string, excerpt: string, href: string, image: string|null}>
     */
    public function listForFrontend(?int $limit = null): array
    {
        return Article::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->when($limit !== null, fn ($query) => $query->limit($limit))
            ->get()
            ->map(fn (Article $article): array => $this->toFrontendItem($article))
            ->values()
            ->all();
    }

    /**
     * @return array{id: string, cat: string, title: string, excerpt: string, href: string, image: string|null}
     */
    public function toFrontendItem(Article $article): array
    {
        return [
            'id' => $article->slug,
            'cat' => $article->category,
            'title' => $article->title,
            'excerpt' => $article->excerpt ?? '',
            'href' => '/resources/'.$article->slug,
            'image' => $article->image_url,
        ];
    }

    public function findActiveBySlug(string $slug): ?Article
    {
        return Article::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * @return array{article: array<string, mixed>, related: list<array<string, mixed>>}|null
     */
    public function detailForFrontend(string $slug): ?array
    {
        $article = $this->findActiveBySlug($slug);

        if ($article === null) {
            return null;
        }

        $related = Article::query()
            ->where('is_active', true)
            ->where('id', '!=', $article->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(2)
            ->get()
            ->map(fn (Article $item): array => $this->toFrontendItem($item))
            ->values()
            ->all();

        return [
            'article' => [
                'slug' => $article->slug,
                'cat' => $article->category,
                'title' => $article->title,
                'excerpt' => $article->excerpt ?? '',
                'paragraphs' => $this->paragraphs($article->body),
                'image' => $article->image_url,
            ],
            'related' => $related,
        ];
    }

    /**
     * Split plain-text body into paragraphs on blank lines.
     *
     * @return list<string>
     */
    public function paragraphs(?string $body): array
    {
        $parts = preg_split('/\R{2,}/', trim((string) $body)) ?: [];

        return array_values(array_filter(
            array_map('trim', $parts),
            fn (string $part): bool => $part !== '',
        ));
    }

    public function nextSortOrder(): int
    {
        return (int) Article::query()->max('sort_order') + 1;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $image = null): Article
    {
        $data['slug'] = $this->uniqueSlug(($data['slug'] ?? null) ?: $data['title']);
        $data['sort_order'] = array_key_exists('sort_order', $data) && $data['sort_order'] !== null
            ? (int) $data['sort_order']
            : $this->nextSortOrder();
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        if ($image instanceof UploadedFile) {
            $data['image'] = $image->store('articles', 'public');
        }

        unset($data['remove_image']);

        return Article::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Article $article, array $data, ?UploadedFile $image = null): Article
    {
        if (array_key_exists('slug', $data)) {
            $data['slug'] = $this->uniqueSlug(
                ($data['slug'] ?? null) ?: $article->slug,
                $article->id,
            );
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $removeImage = (bool) ($data['remove_image'] ?? false);
        unset($data['remove_image']);

        if ($image instanceof UploadedFile) {
            $this->deleteStoredImage($article);
            $data['image'] = $image->store('articles', 'public');
        } elseif ($removeImage) {
            $this->deleteStoredImage($article);
            $data['image'] = null;
        }

        $article->update($data);

        return $article->refresh();
    }

    public function delete(Article $article): void
    {
        $this->deleteStoredImage($article);
        $article->delete();
    }

    public function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $base = $base !== '' ? $base : 'article';
        $slug = $base;
        $suffix = 1;

        while (
            Article::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function deleteStoredImage(Article $article): void
    {
        if ($article->usesStoredUpload() && $article->image !== null) {
            Storage::disk('public')->delete($article->image);
        }
    }
}
