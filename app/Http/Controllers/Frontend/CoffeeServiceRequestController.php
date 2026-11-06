<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoffeeServiceRequest\StoreCoffeeServiceRequestRequest;
use App\Services\CoffeeServiceRequestService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CoffeeServiceRequestController extends Controller
{
    public function __construct(private CoffeeServiceRequestService $coffeeServiceRequests) {}

    public function store(StoreCoffeeServiceRequestRequest $request): RedirectResponse
    {
        $this->coffeeServiceRequests->create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Thanks! We\'ll send coffee options within one business day.',
        ]);

        return redirect()->to('/office-coffee#coffee-form');
    }
}
