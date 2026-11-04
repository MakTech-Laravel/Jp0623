<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MachineCategory\StoreMachineCategoryRequest;
use App\Http\Requests\MachineCategory\UpdateMachineCategoryRequest;
use App\Models\MachineCategory;
use App\Services\MachineCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MachineCategoryController extends Controller
{
    public function __construct(private MachineCategoryService $categories) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('admin/machine-categories/index', [
            'categories' => $this->categories->paginate($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/machine-categories/create');
    }

    public function store(StoreMachineCategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Category created successfully.']);

        return redirect()->route('admin.machine-categories.index');
    }

    public function edit(MachineCategory $machineCategory): Response
    {
        return Inertia::render('admin/machine-categories/edit', [
            'category' => $machineCategory,
        ]);
    }

    public function update(
        UpdateMachineCategoryRequest $request,
        MachineCategory $machineCategory,
    ): RedirectResponse {
        $this->categories->update($machineCategory, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Category updated successfully.']);

        return redirect()->route('admin.machine-categories.index');
    }

    public function destroy(MachineCategory $machineCategory): RedirectResponse
    {
        try {
            $this->categories->delete($machineCategory);
        } catch (ValidationException $exception) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $exception->errors()['category'][0] ?? 'Unable to delete category.',
            ]);

            return redirect()->back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Category deleted successfully.']);

        return redirect()->back();
    }
}
