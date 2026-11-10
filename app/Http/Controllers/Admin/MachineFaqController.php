<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MachineFaq\StoreMachineFaqRequest;
use App\Http\Requests\MachineFaq\UpdateMachineFaqRequest;
use App\Models\MachineFaq;
use App\Services\MachineFaqService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MachineFaqController extends Controller
{
    public function __construct(private MachineFaqService $faqs) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('admin/machine-faqs/index', [
            'faqs' => $this->faqs->paginate($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/machine-faqs/create', [
            'defaultSortOrder' => $this->faqs->nextSortOrder(),
        ]);
    }

    public function store(StoreMachineFaqRequest $request): RedirectResponse
    {
        $this->faqs->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Machine FAQ created successfully.']);

        return redirect()->route('admin.machine-faqs.index');
    }

    public function edit(MachineFaq $machineFaq): Response
    {
        return Inertia::render('admin/machine-faqs/edit', [
            'faq' => $machineFaq,
        ]);
    }

    public function update(UpdateMachineFaqRequest $request, MachineFaq $machineFaq): RedirectResponse
    {
        $this->faqs->update($machineFaq, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Machine FAQ updated successfully.']);

        return redirect()->route('admin.machine-faqs.index');
    }

    public function destroy(MachineFaq $machineFaq): RedirectResponse
    {
        $this->faqs->delete($machineFaq);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Machine FAQ deleted successfully.']);

        return redirect()->back();
    }
}
