<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\OfficeCoffeePageData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class OfficeCoffeeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('frontend/office-coffee', OfficeCoffeePageData::all());
    }
}
