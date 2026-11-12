<?php

namespace App\Services;

use App\Enums\SmsTemplatePurpose;
use App\Models\SmsTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SmsTemplateService
{
    /**
     * @return Collection<int, SmsTemplate>
     */
    public function all(): Collection
    {
        return SmsTemplate::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, SmsTemplate>
     */
    public function manualActive(): Collection
    {
        return SmsTemplate::query()
            ->where('purpose', SmsTemplatePurpose::MANUAL)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function activeFor(SmsTemplatePurpose $purpose): ?SmsTemplate
    {
        return SmsTemplate::query()
            ->where('purpose', $purpose)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    /**
     * @param  array{
     *     name: string,
     *     purpose: string,
     *     body: string,
     *     is_active?: bool,
     *     sort_order?: int
     * }  $data
     */
    public function create(array $data): SmsTemplate
    {
        return DB::transaction(function () use ($data): SmsTemplate {
            $purpose = SmsTemplatePurpose::from($data['purpose']);
            $isActive = (bool) ($data['is_active'] ?? true);

            if ($isActive && $purpose !== SmsTemplatePurpose::MANUAL) {
                $this->deactivatePurpose($purpose);
            }

            return SmsTemplate::query()->create([
                'name' => trim($data['name']),
                'purpose' => $purpose,
                'body' => trim($data['body']),
                'is_active' => $isActive,
                'sort_order' => (int) ($data['sort_order'] ?? $this->nextSortOrder()),
            ]);
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     purpose: string,
     *     body: string,
     *     is_active?: bool,
     *     sort_order?: int
     * }  $data
     */
    public function update(SmsTemplate $template, array $data): SmsTemplate
    {
        return DB::transaction(function () use ($template, $data): SmsTemplate {
            $purpose = SmsTemplatePurpose::from($data['purpose']);
            $isActive = (bool) ($data['is_active'] ?? $template->is_active);

            if ($isActive && $purpose !== SmsTemplatePurpose::MANUAL) {
                $this->deactivatePurpose($purpose, $template->id);
            }

            $template->update([
                'name' => trim($data['name']),
                'purpose' => $purpose,
                'body' => trim($data['body']),
                'is_active' => $isActive,
                'sort_order' => (int) ($data['sort_order'] ?? $template->sort_order),
            ]);

            return $template->refresh();
        });
    }

    public function delete(SmsTemplate $template): void
    {
        $template->delete();
    }

    public function nextSortOrder(): int
    {
        return ((int) SmsTemplate::query()->max('sort_order')) + 1;
    }

    private function deactivatePurpose(SmsTemplatePurpose $purpose, ?int $exceptId = null): void
    {
        SmsTemplate::query()
            ->where('purpose', $purpose)
            ->when($exceptId !== null, fn ($query) => $query->where('id', '!=', $exceptId))
            ->update(['is_active' => false]);
    }
}
