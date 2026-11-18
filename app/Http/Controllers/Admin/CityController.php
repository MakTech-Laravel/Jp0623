<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CitiesTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\City\ImportCitiesRequest;
use App\Http\Requests\City\StoreCityRequest;
use App\Http\Requests\City\UpdateCityRequest;
use App\Models\City;
use App\Services\CityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class CityController extends Controller
{
    public function __construct(private CityService $cities) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('admin/cities/index', [
            'cities' => $this->cities->paginate($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/cities/create', [
            'defaultSortOrder' => $this->cities->nextSortOrder(),
        ]);
    }

    public function store(StoreCityRequest $request): RedirectResponse
    {
        $this->cities->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'City created successfully.']);

        return redirect()->route('admin.cities.index');
    }

    public function edit(City $city): Response
    {
        return Inertia::render('admin/cities/edit', [
            'city' => $city,
        ]);
    }

    public function update(UpdateCityRequest $request, City $city): RedirectResponse
    {
        $this->cities->update($city, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'City updated successfully.']);

        return redirect()->route('admin.cities.index');
    }

    public function destroy(City $city): RedirectResponse
    {
        $this->cities->delete($city);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'City deleted successfully.']);

        return redirect()->back();
    }

    public function import(ImportCitiesRequest $request): RedirectResponse
    {
        try {
            $report = $this->cities->importFromUpload($request->file('file'));
        } catch (Throwable $e) {
            report($e);

            return redirect()->back()->withErrors([
                'file' => 'This file could not be read. Use the template: a header row with city and state columns.',
            ]);
        }

        $summary = sprintf(
            'Import finished: %d added, %d updated, %d skipped.',
            $report['created'],
            $report['updated'],
            $report['skipped'],
        );

        Inertia::flash('toast', [
            'type' => $report['skipped'] > 0 || ($report['created'] + $report['updated']) === 0 ? 'warning' : 'success',
            'message' => $summary,
        ]);
        Inertia::flash('import_report', $report);

        return redirect()->route('admin.cities.index');
    }

    public function template(Request $request): BinaryFileResponse
    {
        $csv = $request->query('format') === 'csv';

        return Excel::download(
            new CitiesTemplateExport,
            'cities-template.'.($csv ? 'csv' : 'xlsx'),
            $csv ? ExcelType::CSV : ExcelType::XLSX,
        );
    }
}
