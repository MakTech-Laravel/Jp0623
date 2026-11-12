<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaymentHistoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(private PaymentHistoryService $payments) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');
        $status = is_string($status) && $status !== '' && $status !== 'all'
            ? $status
            : null;

        return Inertia::render('admin/payments/index', [
            'payments' => $this->payments->paginateForAdmin($search, $status),
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }
}
