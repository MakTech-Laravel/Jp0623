<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Machine\StoreMachineRequest;
use App\Http\Requests\Machine\UpdateMachineRequest;
use App\Models\Machine;
use App\Models\MachineCategory;
use App\Services\MachineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MachineController extends Controller
{
    public function __construct(private MachineService $machines) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->integer('category') ?: null;

        return Inertia::render('admin/machines/index', [
            'machines' => $this->machines->paginate($search, $categoryId),
            'categories' => MachineCategory::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
            'filters' => [
                'search' => $search,
                'category' => $categoryId,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/machines/create', [
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(StoreMachineRequest $request): RedirectResponse
    {
        $this->machines->create(
            $request->safe()->except(['image']),
            $request->hasFile('image') ? $request->file('image') : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Machine created successfully.']);

        return redirect()->route('admin.machines.index');
    }

    public function show(Machine $machine): Response
    {
        $machine->load('category:id,name,slug');

        return Inertia::render('admin/machines/show', [
            'machine' => $machine,
        ]);
    }

    public function edit(Machine $machine): Response
    {
        $machine->load('category:id,name,slug');

        return Inertia::render('admin/machines/edit', [
            'machine' => $machine,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(UpdateMachineRequest $request, Machine $machine): RedirectResponse
    {
        $this->machines->update(
            $machine,
            $request->safe()->except(['image']),
            $request->hasFile('image') ? $request->file('image') : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Machine updated successfully.']);

        return redirect()->route('admin.machines.index');
    }

    public function destroy(Machine $machine): RedirectResponse
    {
        $this->machines->delete($machine);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Machine deleted successfully.']);

        return redirect()->back();
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function categoryOptions(): array
    {
        return MachineCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (MachineCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->all();
    }
}
