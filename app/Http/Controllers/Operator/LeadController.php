<?php

namespace App\Http\Controllers\Operator;

use App\Demo\OperatorDashboardData;
use App\Http\Controllers\Controller;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function __construct(private LeadService $leads) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing('operatorProfile');

        return Inertia::render('operator/leads/index', [
            'leads' => $this->leads->availableFor($user),
            'primaryCity' => $user->operatorProfile?->primary_city ?: 'Not set',
        ]);
    }

    public function purchased(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing('operatorProfile');

        return Inertia::render('operator/leads/purchased', [
            'purchasedLeads' => $this->leads->purchasedBy($user),
            'primaryCity' => $user->operatorProfile?->primary_city ?: 'Not set',
        ]);
    }

    public function purchase(Request $request, string $source, int $lead): RedirectResponse
    {
        $this->leads->purchase($request->user(), $source, $lead);

        return redirect()->route('leads.purchased');
    }

    public function tickets(): Response
    {
        return Inertia::render('operator/leads/tickets', [
            'tickets' => OperatorDashboardData::tickets(),
        ]);
    }

    public function faq(): Response
    {
        return Inertia::render('operator/leads/faq', [
            'faqs' => OperatorDashboardData::faq(),
        ]);
    }
}
