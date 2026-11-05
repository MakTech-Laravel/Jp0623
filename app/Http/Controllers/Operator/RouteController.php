<?php

namespace App\Http\Controllers\Operator;

use App\Demo\OperatorDashboardData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class RouteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('operator/routes/index', [
            'routes' => OperatorDashboardData::routes(),
        ]);
    }

    public function sell(): Response
    {
        return Inertia::render('operator/routes/sell');
    }
}
