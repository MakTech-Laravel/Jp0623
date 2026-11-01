<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\MicroMarketsPageData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class MicroMarketsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('frontend/micro-markets', MicroMarketsPageData::all());
    }
}
