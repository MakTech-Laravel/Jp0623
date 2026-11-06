<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendingRequest\UpdateVendingRequestRequest;
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

        return Inertia::render('admin/vending-requests/index', [
            'requests' => $this->vendingRequests->paginate($search, $status),
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function show(VendingRequest $vendingRequest): Response
    {
        return Inertia::render('admin/vending-requests/show', [
            'vendingRequest' => $vendingRequest,
        ]);
    }

    public function update(
        UpdateVendingRequestRequest $request,
        VendingRequest $vendingRequest,
    ): RedirectResponse {
        $this->vendingRequests->updateStatus(
            $vendingRequest,
            $request->validated('status'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Request status updated.']);

        return redirect()->back();
    }

    public function destroy(VendingRequest $vendingRequest): RedirectResponse
    {
        $this->vendingRequests->delete($vendingRequest);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Request deleted.']);

        return redirect()->route('admin.vending-requests.index');
    }
}
