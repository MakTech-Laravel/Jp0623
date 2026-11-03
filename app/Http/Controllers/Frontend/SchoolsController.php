<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\SchoolsPageData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class SchoolsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('frontend/schools', SchoolsPageData::all());
    }
}
