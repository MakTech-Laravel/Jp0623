<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\CitiesPageData;
use App\Http\Controllers\Controller;
use App\Services\CityService;
use Inertia\Inertia;
use Inertia\Response;

class CitiesController extends Controller
{
    public function __construct(private CityService $cities) {}

    public function index(): Response
    {
        $page = CitiesPageData::all();
        $page['cities']['items'] = $this->cities->listForFrontend();

        return Inertia::render('frontend/cities', $page);
    }
}
