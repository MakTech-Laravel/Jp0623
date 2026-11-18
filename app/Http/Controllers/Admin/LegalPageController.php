<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LegalPage\UpdateLegalPageRequest;
use App\Models\LegalPage;
use App\Services\LegalPageService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LegalPageController extends Controller
{
    public function __construct(private LegalPageService $legalPages) {}

    public function index(): Response
    {
        return Inertia::render('admin/legal-pages/index', [
            'legalPages' => $this->legalPages->listForAdmin()->map(fn (LegalPage $legalPage): array => [
                'id' => $legalPage->id,
                'slug' => $legalPage->slug,
                'title' => $legalPage->title,
                'last_updated_on' => $legalPage->last_updated_on?->toDateString(),
                'sections_count' => count($legalPage->sections),
            ])->values(),
        ]);
    }

    public function edit(LegalPage $legalPage): Response
    {
        return Inertia::render('admin/legal-pages/edit', [
            'legalPage' => [
                'id' => $legalPage->id,
                'slug' => $legalPage->slug,
                'title' => $legalPage->title,
                'last_updated_on' => $legalPage->last_updated_on?->toDateString(),
                'sections' => $legalPage->sections,
            ],
        ]);
    }

    public function update(UpdateLegalPageRequest $request, LegalPage $legalPage): RedirectResponse
    {
        $this->legalPages->update($legalPage, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Legal page updated successfully.']);

        return redirect()->route('admin.legal-pages.index');
    }
}
