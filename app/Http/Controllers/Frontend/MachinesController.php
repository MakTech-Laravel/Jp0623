<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\MachinesPageData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class MachinesController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('frontend/machines', MachinesPageData::all());
    }
}
