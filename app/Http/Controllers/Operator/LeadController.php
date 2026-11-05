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

        return Inertia::render('operator/leads/index', [
            'leads' => $data['leads'],
            'primaryCity' => $data['primaryCity'],
        ]);
    }

    public function purchased(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing('operatorProfile');

        return Inertia::render('operator/leads/purchased', [
            'purchasedLeads' => OperatorDashboardData::purchasedLeads(),
            'primaryCity' => $user->operatorProfile?->primary_city ?: 'Houston, TX',
        ]);
    }

    public function tickets(): Response
    {
        return Inertia::render('operator/leads/tickets', [
            'tickets' => OperatorDashboardData::tickets(),
        ]);
    }

    public function submit(): Response
    {
        return Inertia::render('operator/leads/submit');
    }

    public function commission(): Response
    {
        return Inertia::render(
            'operator/leads/commission',
            OperatorDashboardData::commission(),
        );
    }

    public function faq(): Response
    {
        return Inertia::render('operator/leads/faq', [
            'faqs' => OperatorDashboardData::faq(),
        ]);
    }
}
