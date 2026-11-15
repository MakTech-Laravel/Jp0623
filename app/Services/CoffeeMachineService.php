<?php

namespace App\Services;

use App\Models\CoffeeMachine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CoffeeMachineService
{
    /**
     * @return LengthAwarePaginator<int, CoffeeMachine>
     */
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return CoffeeMachine::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('tag', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return list<array{title: string, text: string, tag: string|null, image: string, href: string}>
     */
    public function listForFrontend(): array
    {
        return CoffeeMachine::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CoffeeMachine $machine): array => [
                'title' => $machine->title,
                'text' => $machine->text ?? '',
                'tag' => $machine->tag,
                'image' => $machine->image_url ?? '',
                'href' => '#coffee-form',
            ])
            ->values()
            ->all();
    }

    public function nextSortOrder(): int
    {
        return (int) CoffeeMachine::query()->max('sort_order') + 1;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $image = null): CoffeeMachine
    {
        $data['sort_order'] = array_key_exists('sort_order', $data) && $data['sort_order'] !== null
            ? (int) $data['sort_order']
            : $this->nextSortOrder();
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        if ($image instanceof UploadedFile) {
            $data['image'] = $image->store('coffee-machines', 'public');
        }

        unset($data['remove_image']);

        return CoffeeMachine::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CoffeeMachine $coffeeMachine, array $data, ?UploadedFile $image = null): CoffeeMachine
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $removeImage = (bool) ($data['remove_image'] ?? false);
        unset($data['remove_image']);

        if ($image instanceof UploadedFile) {
            $this->deleteStoredImage($coffeeMachine);
            $data['image'] = $image->store('coffee-machines', 'public');
        } elseif ($removeImage) {
            $this->deleteStoredImage($coffeeMachine);
            $data['image'] = null;
        }

        $coffeeMachine->update($data);

        return $coffeeMachine->refresh();
    }

    public function delete(CoffeeMachine $coffeeMachine): void
    {
        $this->deleteStoredImage($coffeeMachine);
        $coffeeMachine->delete();
    }

    private function deleteStoredImage(CoffeeMachine $coffeeMachine): void
    {
        if ($coffeeMachine->usesStoredUpload() && $coffeeMachine->image !== null) {
            Storage::disk('public')->delete($coffeeMachine->image);
        }
    }
}
