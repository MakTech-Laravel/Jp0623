<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Services\MembershipService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function __construct(private MembershipService $memberships) {}

    public function index(Request $request): Response
    {
        return Inertia::render(
            'operator/subscription/index',
            $this->memberships->pagePayload($request->user()),
        );
    }
}
