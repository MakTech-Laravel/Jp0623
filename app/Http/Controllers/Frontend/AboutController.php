<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\AboutPageData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('frontend/about', AboutPageData::all());
    }
}
