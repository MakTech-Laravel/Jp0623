<?php

namespace App\Http\Middleware;

use App\Services\MembershipService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOperatorMembershipActive
{
    public function __construct(private MembershipService $memberships) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $this->memberships->userCanAccessMarketplace($user)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'An active membership is required.');
        }

        return redirect()
            ->route('subscription.index')
            ->with('inertia.flash_data.toast', [
                'type' => 'error',
                'message' => 'An active membership is required to access the lead marketplace.',
            ]);
    }
}
