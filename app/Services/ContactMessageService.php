<?php

namespace App\Services;

use App\Models\ContactMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactMessageService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ContactMessage
    {
        return ContactMessage::create([
            ...$data,
            'status' => ContactMessage::STATUS_NEW,
            'terms_accepted' => (bool) ($data['terms_accepted'] ?? false),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, ContactMessage>
     */
    public function paginate(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 15,
    ): LengthAwarePaginator {
        $search = trim((string) $search);

        return ContactMessage::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('topic', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(ContactMessage $message, array $data): ContactMessage
    {
        $message->update($data);

        return $message->refresh();
    }

    public function delete(ContactMessage $message): void
    {
        $message->delete();
    }
}
