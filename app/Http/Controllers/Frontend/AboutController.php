<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\AboutPageData;
use App\Http\Controllers\Controller;
use App\Services\AboutFaqService;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __construct(private AboutFaqService $faqs) {}

    public function index(): Response
    {
        $page = AboutPageData::all();
        $page['faqs']['items'] = $this->faqs->listForFrontend();

        return Inertia::render('frontend/about', $page);
    }
}
