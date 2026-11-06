<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboardController;
use App\Support\AdminDashboardData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->hasAnyRole([
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN->value,
        ])) {
            return Inertia::render('admin/dashboard', AdminDashboardData::make());
        }

        return app(OperatorDashboardController::class)->index($request);
    }
}
