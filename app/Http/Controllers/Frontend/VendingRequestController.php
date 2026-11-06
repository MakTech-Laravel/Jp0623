<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendingRequest\StoreVendingRequestRequest;
use App\Services\VendingRequestService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class VendingRequestController extends Controller
{
    public function __construct(private VendingRequestService $vendingRequests) {}

    public function store(StoreVendingRequestRequest $request): RedirectResponse
    {
        $this->vendingRequests->create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Thanks! Our team will email you within one business day to confirm your request.',
        ]);

        return redirect()->to('/#request-sec');
    }
}
