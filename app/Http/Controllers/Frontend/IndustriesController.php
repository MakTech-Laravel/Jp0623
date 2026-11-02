<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\IndustriesPageData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class IndustriesController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('frontend/industries', IndustriesPageData::index());
    }

    public function show(string $industry): Response
    {
        $page = IndustriesPageData::show($industry);

        abort_if($page === null, 404);

        return Inertia::render('frontend/industries/show', $page);
    }
}
