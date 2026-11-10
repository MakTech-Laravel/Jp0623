<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeCoffeeFaq\StoreOfficeCoffeeFaqRequest;
use App\Http\Requests\OfficeCoffeeFaq\UpdateOfficeCoffeeFaqRequest;
use App\Models\OfficeCoffeeFaq;
use App\Services\OfficeCoffeeFaqService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OfficeCoffeeFaqController extends Controller
{
    public function __construct(private OfficeCoffeeFaqService $faqs) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('admin/office-coffee-faqs/index', [
            'faqs' => $this->faqs->paginate($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/office-coffee-faqs/create', [
            'defaultSortOrder' => $this->faqs->nextSortOrder(),
        ]);
    }

    public function store(StoreOfficeCoffeeFaqRequest $request): RedirectResponse
    {
        $this->faqs->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Office coffee FAQ created successfully.']);

        return redirect()->route('admin.office-coffee-faqs.index');
    }

    public function edit(OfficeCoffeeFaq $officeCoffeeFaq): Response
    {
        return Inertia::render('admin/office-coffee-faqs/edit', [
            'faq' => $officeCoffeeFaq,
        ]);
    }

    public function update(
        UpdateOfficeCoffeeFaqRequest $request,
        OfficeCoffeeFaq $officeCoffeeFaq,
    ): RedirectResponse {
        $this->faqs->update($officeCoffeeFaq, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Office coffee FAQ updated successfully.']);

        return redirect()->route('admin.office-coffee-faqs.index');
    }

    public function destroy(OfficeCoffeeFaq $officeCoffeeFaq): RedirectResponse
    {
        $this->faqs->delete($officeCoffeeFaq);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Office coffee FAQ deleted successfully.']);

        return redirect()->back();
    }
}
