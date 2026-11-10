<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HomeFaq\StoreHomeFaqRequest;
use App\Http\Requests\HomeFaq\UpdateHomeFaqRequest;
use App\Models\HomeFaq;
use App\Services\HomeFaqService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeFaqController extends Controller
{
    public function __construct(private HomeFaqService $faqs) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('admin/home-faqs/index', [
            'faqs' => $this->faqs->paginate($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/home-faqs/create', [
            'defaultSortOrder' => $this->faqs->nextSortOrder(),
        ]);
    }

    public function store(StoreHomeFaqRequest $request): RedirectResponse
    {
        $this->faqs->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Home FAQ created successfully.']);

        return redirect()->route('admin.home-faqs.index');
    }

    public function edit(HomeFaq $homeFaq): Response
    {
        return Inertia::render('admin/home-faqs/edit', [
            'faq' => $homeFaq,
        ]);
    }

    public function update(UpdateHomeFaqRequest $request, HomeFaq $homeFaq): RedirectResponse
    {
        $this->faqs->update($homeFaq, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Home FAQ updated successfully.']);

        return redirect()->route('admin.home-faqs.index');
    }

    public function destroy(HomeFaq $homeFaq): RedirectResponse
    {
        $this->faqs->delete($homeFaq);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Home FAQ deleted successfully.']);

        return redirect()->back();
    }
}
