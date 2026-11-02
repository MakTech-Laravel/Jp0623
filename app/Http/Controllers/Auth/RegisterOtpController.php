<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegisterOtpMail;
use App\Support\RegisterOtp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RegisterOtpController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));
        $code = RegisterOtp::generateCode();

        RegisterOtp::store($email, $code);

        Mail::to($email)->send(new RegisterOtpMail($code));

        return response()->json([
            'message' => 'Verification code sent.',
        ]);
    }
}
