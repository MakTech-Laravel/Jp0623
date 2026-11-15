<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoffeeMachine\StoreCoffeeMachineRequest;
use App\Http\Requests\CoffeeMachine\UpdateCoffeeMachineRequest;
use App\Models\CoffeeMachine;
use App\Services\CoffeeMachineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CoffeeMachineController extends Controller
{
    public function __construct(private CoffeeMachineService $coffeeMachines) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('admin/coffee-machines/index', [
            'coffeeMachines' => $this->coffeeMachines->paginate($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/coffee-machines/create', [
            'defaultSortOrder' => $this->coffeeMachines->nextSortOrder(),
        ]);
    }

    public function store(StoreCoffeeMachineRequest $request): RedirectResponse
    {
        $this->coffeeMachines->create(
            $request->safe()->except(['image']),
            $request->hasFile('image') ? $request->file('image') : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Coffee machine created successfully.']);

        return redirect()->route('admin.coffee-machines.index');
    }

    public function edit(CoffeeMachine $coffeeMachine): Response
    {
        return Inertia::render('admin/coffee-machines/edit', [
            'coffeeMachine' => $coffeeMachine,
        ]);
    }

    public function update(UpdateCoffeeMachineRequest $request, CoffeeMachine $coffeeMachine): RedirectResponse
    {
        $this->coffeeMachines->update(
            $coffeeMachine,
            $request->safe()->except(['image']),
            $request->hasFile('image') ? $request->file('image') : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Coffee machine updated successfully.']);

        return redirect()->route('admin.coffee-machines.index');
    }

    public function destroy(CoffeeMachine $coffeeMachine): RedirectResponse
    {
        $this->coffeeMachines->delete($coffeeMachine);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Coffee machine deleted successfully.']);

        return redirect()->back();
    }
}
