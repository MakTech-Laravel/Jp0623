<?php

namespace App\Http\Controllers\Operator;

use App\Exceptions\Payments\PaymentFailedException;
use App\Exceptions\Payments\SquareNotConfiguredException;
use App\Http\Controllers\Controller;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MembershipCheckoutController extends Controller
{
    public function __construct(private MembershipService $memberships) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_id' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->memberships->checkout($request->user(), $validated['source_id']);
        } catch (SquareNotConfiguredException) {
            throw ValidationException::withMessages([
                'source_id' => 'Payments are temporarily unavailable. Please try again later.',
            ]);
        } catch (PaymentFailedException $exception) {
            throw ValidationException::withMessages([
                'source_id' => $exception->getMessage(),
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Membership activated. Thank you!',
        ]);

        return redirect()->route('subscription.index');
    }
}
