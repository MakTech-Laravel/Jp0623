<?php

namespace App\Services;

use App\Models\CoffeeType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CoffeeTypeService
{
    /**
     * @return LengthAwarePaginator<int, CoffeeType>
     */
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return CoffeeType::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('label', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return list<array{label: string, href: string}>
     */
    public function listForFrontend(): array
    {
        return CoffeeType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CoffeeType $type): array => [
                'label' => $type->label,
                'href' => '#coffee-form',
            ])
            ->values()
            ->all();
    }

    public function nextSortOrder(): int
    {
        return (int) CoffeeType::query()->max('sort_order') + 1;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): CoffeeType
    {
        $data['sort_order'] = array_key_exists('sort_order', $data) && $data['sort_order'] !== null
            ? (int) $data['sort_order']
            : $this->nextSortOrder();
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        return CoffeeType::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CoffeeType $coffeeType, array $data): CoffeeType
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $coffeeType->update($data);

        return $coffeeType->refresh();
    }

    public function delete(CoffeeType $coffeeType): void
    {
        $coffeeType->delete();
    }
}
