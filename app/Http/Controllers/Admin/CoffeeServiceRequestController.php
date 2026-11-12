<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoffeeServiceRequest\UpdateCoffeeServiceRequestRequest;
use App\Models\CoffeeServiceRequest;
use App\Services\CoffeeServiceRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CoffeeServiceRequestController extends Controller
{
    public function __construct(private CoffeeServiceRequestService $coffeeServiceRequests) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');
        $status = is_string($status) && $status !== '' && $status !== 'all'
            ? $status
            : null;
        $sale = $request->query('sale');
        $sale = is_string($sale) && $sale !== '' && $sale !== 'all'
            ? $sale
            : null;

        return Inertia::render('admin/coffee-service-requests/index', [
            'requests' => $this->coffeeServiceRequests->paginate($search, $status, $sale),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'sale' => $sale,
            ],
        ]);
    }

    public function show(CoffeeServiceRequest $coffeeServiceRequest): Response
    {
        $leadBuyer = $this->coffeeServiceRequests->prepareForAdminShow($coffeeServiceRequest);

        return Inertia::render('admin/coffee-service-requests/show', [
            'coffeeServiceRequest' => $coffeeServiceRequest,
            'leadBuyer' => $leadBuyer,
        ]);
    }

    public function update(
        UpdateCoffeeServiceRequestRequest $request,
        CoffeeServiceRequest $coffeeServiceRequest,
    ): RedirectResponse {
        $this->coffeeServiceRequests->updateStatus(
            $coffeeServiceRequest,
            $request->validated('status'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Request status updated.']);

        return redirect()->back();
    }

    public function destroy(CoffeeServiceRequest $coffeeServiceRequest): RedirectResponse
    {
        $this->coffeeServiceRequests->delete($coffeeServiceRequest);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Request deleted.']);

        return redirect()->route('admin.coffee-service-requests.index');
    }
}
