<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewService
{
    /**
     * @return LengthAwarePaginator<int, Review>
     */
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return Review::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('quote', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%")
                        ->orWhere('org', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return list<array{id: int, stars: string, quote: string, role: string, org: string}>
     */
    public function listForFrontend(): array
    {
        return Review::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Review $review): array => [
                'id' => $review->id,
                'stars' => str_repeat('★', max(0, min(5, $review->rating))),
                'quote' => $review->quote,
                'role' => $review->role,
                'org' => $review->org,
            ])
            ->values()
            ->all();
    }

    public function nextSortOrder(): int
    {
        return (int) Review::query()->max('sort_order') + 1;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Review
    {
        $data['sort_order'] = array_key_exists('sort_order', $data) && $data['sort_order'] !== null
            ? (int) $data['sort_order']
            : $this->nextSortOrder();
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        return Review::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Review $review, array $data): Review
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $review->update($data);

        return $review->refresh();
    }

    public function delete(Review $review): void
    {
        $review->delete();
    }
}
