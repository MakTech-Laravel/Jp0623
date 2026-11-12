<?php

namespace App\Http\Controllers\Operator;

use App\Demo\OperatorDashboardData;
use App\Exceptions\Payments\LeadUnavailableException;
use App\Exceptions\Payments\PaymentFailedException;
use App\Exceptions\Payments\SquareNotConfiguredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\PurchaseLeadRequest;
use App\Services\LeadPurchaseService;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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

    /**
     * Buy a lead with the card token from Square's Web Payments SDK.
     */
    public function purchase(
        PurchaseLeadRequest $request,
        LeadPurchaseService $purchases,
        string $source,
        int $lead,
    ): RedirectResponse {
        try {
            $purchase = $purchases->checkout(
                $request->user(),
                $source,
                $lead,
                (string) $request->validated('source_id'),
            );
        } catch (LeadUnavailableException|PaymentFailedException $exception) {
            throw ValidationException::withMessages(['payment' => $exception->getMessage()]);
        } catch (SquareNotConfiguredException $exception) {
            throw ValidationException::withMessages(['payment' => $exception->userMessage()]);
        }

        if ($purchase->isPaid()) {
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Lead purchased. Contact details are unlocked.']);
        } else {
            Inertia::flash('toast', ['type' => 'warning', 'message' => 'Payment is processing. Details unlock as soon as it is confirmed.']);
        }

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
