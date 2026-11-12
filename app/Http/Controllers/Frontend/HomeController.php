<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\HomePageData;
use App\Http\Controllers\Controller;
use App\Services\HomeFaqService;
use App\Services\LocationTypeService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private HomeFaqService $faqs,
        private LocationTypeService $locationTypes,
    ) {}

    public function index(): Response
    {
        $page = HomePageData::all();
        $page['faqs']['items'] = $this->faqs->listForFrontend();
        $page['requestForm']['locationTypes'] = $this->locationTypes->listActiveNamesForFrontend();

        return Inertia::render('frontend/home', $page);
    }
}
