<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VendingRequest\StoreVendingRequestRequest;
use App\Http\Requests\VendingRequest\UpdateVendingRequestRequest;
use App\Models\LocationType;
use App\Models\VendingRequest;
use App\Services\VendingRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendingRequestController extends Controller
{
    public function __construct(private VendingRequestService $vendingRequests) {}

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

        return Inertia::render('admin/vending-requests/index', [
            'requests' => $this->vendingRequests->paginate($search, $status, $sale),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'sale' => $sale,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/vending-requests/create', [
            'defaultPrice' => (int) config('leads.price'),
            'locationTypes' => LocationType::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->values()
                ->all(),
            'placements' => VendingRequest::PLACEMENTS,
            'machineInterests' => VendingRequest::MACHINE_INTERESTS,
            'machineCounts' => VendingRequest::MACHINE_COUNTS,
            'existingVending' => VendingRequest::EXISTING_VENDING,
            'yesNoOptions' => VendingRequest::YES_NO,
        ]);
    }

    public function store(StoreVendingRequestRequest $request): RedirectResponse
    {
        $vendingRequest = $this->vendingRequests->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Vending request created.']);

        return redirect()->route('admin.vending-requests.show', $vendingRequest);
    }

    public function show(VendingRequest $vendingRequest): Response
    {
        $leadBuyer = $this->vendingRequests->prepareForAdminShow($vendingRequest);

        return Inertia::render('admin/vending-requests/show', [
            'vendingRequest' => $vendingRequest,
            'leadBuyer' => $leadBuyer,
        ]);
    }

    public function update(
        UpdateVendingRequestRequest $request,
        VendingRequest $vendingRequest,
    ): RedirectResponse {
        $this->vendingRequests->update($vendingRequest, $request->validated());

        $message = $request->exists('price') && ! $request->exists('status')
            ? 'Lead price updated.'
            : 'Request updated.';

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return redirect()->back();
    }

    public function destroy(VendingRequest $vendingRequest): RedirectResponse
    {
        $this->vendingRequests->delete($vendingRequest);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Request deleted.']);

        return redirect()->route('admin.vending-requests.index');
    }
}
