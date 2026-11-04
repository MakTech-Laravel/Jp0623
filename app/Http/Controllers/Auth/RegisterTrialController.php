<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegisterTrialController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->operatorProfile;

        if ($profile !== null && $profile->trial_started_at === null) {
            $profile->update([
                'trial_started_at' => now(),
            ]);
        }

        return redirect()->route('dashboard');
    }
}
