<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\CreateOperatorAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterAccountController extends Controller
{
    public function store(Request $request, CreateOperatorAccount $creator): JsonResponse
    {
        $creator->create($request->all());

        return response()->json([
            'message' => 'Account created. Verification code sent.',
        ]);
    }
}
