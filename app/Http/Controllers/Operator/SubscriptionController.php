<?php

namespace App\Http\Controllers\Operator;

use App\Demo\OperatorDashboardData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('subscription/index', [
            'subscription' => OperatorDashboardData::subscription(),
        ]);
    }
}
