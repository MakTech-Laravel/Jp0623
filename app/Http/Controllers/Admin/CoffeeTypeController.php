<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoffeeType\StoreCoffeeTypeRequest;
use App\Http\Requests\CoffeeType\UpdateCoffeeTypeRequest;
use App\Models\CoffeeType;
use App\Services\CoffeeTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CoffeeTypeController extends Controller
{
    public function __construct(private CoffeeTypeService $coffeeTypes) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('admin/coffee-types/index', [
            'coffeeTypes' => $this->coffeeTypes->paginate($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/coffee-types/create', [
            'defaultSortOrder' => $this->coffeeTypes->nextSortOrder(),
        ]);
    }

    public function store(StoreCoffeeTypeRequest $request): RedirectResponse
    {
        $this->coffeeTypes->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Coffee type created successfully.']);

        return redirect()->route('admin.coffee-types.index');
    }

    public function edit(CoffeeType $coffeeType): Response
    {
        return Inertia::render('admin/coffee-types/edit', [
            'coffeeType' => $coffeeType,
        ]);
    }

    public function update(UpdateCoffeeTypeRequest $request, CoffeeType $coffeeType): RedirectResponse
    {
        $this->coffeeTypes->update($coffeeType, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Coffee type updated successfully.']);

        return redirect()->route('admin.coffee-types.index');
    }

    public function destroy(CoffeeType $coffeeType): RedirectResponse
    {
        $this->coffeeTypes->delete($coffeeType);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Coffee type deleted successfully.']);

        return redirect()->back();
    }
}
