<?php

namespace App\Http\Controllers\Operator;

use App\Demo\OperatorDashboardData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing('operatorProfile');

        return Inertia::render('operator/account/index', [
            'account' => OperatorDashboardData::account($user),
        ]);
    }

    public function location(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing('operatorProfile');

        return Inertia::render(
            'operator/account/location',
            OperatorDashboardData::locationSettings($user),
        );
    }
}
