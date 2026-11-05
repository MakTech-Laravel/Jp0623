<?php

namespace App\Http\Controllers\Operator;

use App\Demo\OperatorDashboardData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing('operatorProfile');

        return Inertia::render(
            'operator/dashboard',
            OperatorDashboardData::for($user),
        );
    }
}
