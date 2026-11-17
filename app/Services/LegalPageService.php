<?php

namespace App\Services;

use App\Models\LegalPage;
use Illuminate\Database\Eloquent\Collection;

class LegalPageService
{
    /**
     * @return Collection<int, LegalPage>
     */
    public function listForAdmin(): Collection
    {
        return LegalPage::query()->orderBy('id')->get();
    }

    public function findBySlug(string $slug): ?LegalPage
    {
        return LegalPage::query()->where('slug', $slug)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(LegalPage $legalPage, array $data): LegalPage
    {
        $legalPage->update([
            'title' => $data['title'],
            'last_updated_on' => $data['last_updated_on'] ?? null,
            'sections' => array_values(array_map(
                fn (array $section): array => [
                    'heading' => filled($section['heading'] ?? null) ? $section['heading'] : null,
                    'body' => $section['body'],
                ],
                $data['sections'],
            )),
        ]);

        return $legalPage;
    }

    /**
     * @return array{hero: array{title: string, lead: string|null}, sections: list<array{heading: string|null, body: string}>}
     */
    public function toFrontendPayload(LegalPage $legalPage): array
    {
        return [
            'hero' => [
                'title' => $legalPage->title,
                'lead' => $legalPage->last_updated_on
                    ? 'Last updated '.$legalPage->last_updated_on->format('F j, Y').'.'
                    : null,
            ],
            'sections' => $legalPage->sections,
        ];
    }
}
