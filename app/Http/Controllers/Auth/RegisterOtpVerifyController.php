<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\RegisterOtp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegisterOtpVerifyController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_otp' => ['required', 'digits:6'],
        ]);

        $user = $request->user();
        $email = strtolower(trim($user->email));

        if (! RegisterOtp::verify($email, $validated['email_otp'])) {
            throw ValidationException::withMessages([
                'email_otp' => __('The verification code is invalid or has expired.'),
            ]);
        }

        RegisterOtp::forget($email);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $user->operatorProfile()?->update([
            'email_code_confirmed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Email verified.',
        ]);
    }
}
