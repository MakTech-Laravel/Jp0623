<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\ResourcesPageData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ResourcesController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('frontend/resources', ResourcesPageData::all());
    }
}
