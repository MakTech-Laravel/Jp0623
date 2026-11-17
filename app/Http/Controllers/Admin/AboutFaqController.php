<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AboutFaq\StoreAboutFaqRequest;
use App\Http\Requests\AboutFaq\UpdateAboutFaqRequest;
use App\Models\AboutFaq;
use App\Services\AboutFaqService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AboutFaqController extends Controller
{
    public function __construct(private AboutFaqService $faqs) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('admin/about-faqs/index', [
            'faqs' => $this->faqs->paginate($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/about-faqs/create', [
            'defaultSortOrder' => $this->faqs->nextSortOrder(),
        ]);
    }

    public function store(StoreAboutFaqRequest $request): RedirectResponse
    {
        $this->faqs->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'About FAQ created successfully.']);

        return redirect()->route('admin.about-faqs.index');
    }

    public function edit(AboutFaq $aboutFaq): Response
    {
        return Inertia::render('admin/about-faqs/edit', [
            'faq' => $aboutFaq,
        ]);
    }

    public function update(UpdateAboutFaqRequest $request, AboutFaq $aboutFaq): RedirectResponse
    {
        $this->faqs->update($aboutFaq, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'About FAQ updated successfully.']);

        return redirect()->route('admin.about-faqs.index');
    }

    public function destroy(AboutFaq $aboutFaq): RedirectResponse
    {
        $this->faqs->delete($aboutFaq);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'About FAQ deleted successfully.']);

        return redirect()->back();
    }
}
