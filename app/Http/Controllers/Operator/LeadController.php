<?php

namespace App\Http\Controllers\Operator;

use App\Demo\OperatorDashboardData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing('operatorProfile');

        $data = OperatorDashboardData::for($user);

        return Inertia::render('leads/index', [
            'leads' => $data['leads'],
            'primaryCity' => $data['primaryCity'],
        ]);
    }

    public function purchased(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing('operatorProfile');

        return Inertia::render('leads/purchased', [
            'purchasedLeads' => OperatorDashboardData::purchasedLeads(),
            'primaryCity' => $user->operatorProfile?->primary_city ?: 'Houston, TX',
        ]);
    }
}
