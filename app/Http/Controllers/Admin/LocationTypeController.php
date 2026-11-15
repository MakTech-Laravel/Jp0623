<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LocationType\StoreLocationTypeRequest;
use App\Http\Requests\LocationType\UpdateLocationTypeRequest;
use App\Models\LocationType;
use App\Services\LocationTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationTypeController extends Controller
{
    public function __construct(private LocationTypeService $locationTypes) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('admin/location-types/index', [
            'locationTypes' => $this->locationTypes->paginate($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/location-types/create', [
            'defaultSortOrder' => $this->locationTypes->nextSortOrder(),
            'kindOptions' => $this->locationTypes->machineKindOptions(),
        ]);
    }

    public function store(StoreLocationTypeRequest $request): RedirectResponse
    {
        $this->locationTypes->create(
            $request->safe()->except(['image']),
            $request->hasFile('image') ? $request->file('image') : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Location type created successfully.']);

        return redirect()->route('admin.location-types.index');
    }

    public function edit(LocationType $locationType): Response
    {
        return Inertia::render('admin/location-types/edit', [
            'locationType' => $locationType,
            'kindOptions' => $this->locationTypes->machineKindOptions(),
        ]);
    }

    public function update(UpdateLocationTypeRequest $request, LocationType $locationType): RedirectResponse
    {
        $this->locationTypes->update(
            $locationType,
            $request->safe()->except(['image']),
            $request->hasFile('image') ? $request->file('image') : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Location type updated successfully.']);

        return redirect()->route('admin.location-types.index');
    }

    public function destroy(LocationType $locationType): RedirectResponse
    {
        $this->locationTypes->delete($locationType);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Location type deleted successfully.']);

        return redirect()->back();
    }
}
