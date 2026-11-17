<?php

namespace App\Services;

use App\Models\AboutFaq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AboutFaqService
{
    /**
     * @return LengthAwarePaginator<int, AboutFaq>
     */
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return AboutFaq::query()
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
        return AboutFaq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['question', 'answer'])
            ->map(fn (AboutFaq $faq): array => [
                'q' => $faq->question,
                'a' => $faq->answer,
            ])
            ->values()
            ->all();
    }

    public function nextSortOrder(): int
    {
        return (int) AboutFaq::query()->max('sort_order') + 1;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): AboutFaq
    {
        $data['sort_order'] = array_key_exists('sort_order', $data) && $data['sort_order'] !== null
            ? (int) $data['sort_order']
            : $this->nextSortOrder();
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        return AboutFaq::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(AboutFaq $faq, array $data): AboutFaq
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $faq->update($data);

        return $faq->refresh();
    }

    public function delete(AboutFaq $faq): void
    {
        $faq->delete();
    }
}
