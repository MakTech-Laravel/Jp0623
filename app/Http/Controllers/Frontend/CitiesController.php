<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\CitiesPageData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CitiesController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('frontend/cities', CitiesPageData::index());
    }

    public function show(string $city): Response
    {
        $page = CitiesPageData::show($city);

        abort_if($page === null, 404);

        return Inertia::render('frontend/cities/show', $page);
    }
}
