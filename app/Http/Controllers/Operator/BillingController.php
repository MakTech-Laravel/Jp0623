<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Services\PaymentHistoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function __construct(private PaymentHistoryService $payments) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('operator/billing/index', [
            'lastPaidCard' => $this->payments->latestPaidCardFor($user),
            'invoices' => $this->payments->invoicesFor($user),
        ]);
    }
}
