<?php

namespace App\Services;

use App\Models\HomeFaq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HomeFaqService
{
    /**
     * @return LengthAwarePaginator<int, HomeFaq>
     */
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return HomeFaq::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('question', 'like', "%{$search}%")
                        ->orWhere('answer', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return list<array{q: string, a: string}>
     */
    public function listForFrontend(): array
    {
        return HomeFaq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['question', 'answer'])
            ->map(fn (HomeFaq $faq): array => [
                'q' => $faq->question,
                'a' => $faq->answer,
            ])
            ->values()
            ->all();
    }

    public function nextSortOrder(): int
    {
        return (int) HomeFaq::query()->max('sort_order') + 1;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): HomeFaq
    {
        $data['sort_order'] = array_key_exists('sort_order', $data) && $data['sort_order'] !== null
            ? (int) $data['sort_order']
            : $this->nextSortOrder();
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        return HomeFaq::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(HomeFaq $faq, array $data): HomeFaq
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $faq->update($data);

        return $faq->refresh();
    }

    public function delete(HomeFaq $faq): void
    {
        $faq->delete();
    }
}
