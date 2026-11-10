<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\OfficeCoffeePageData;
use App\Http\Controllers\Controller;
use App\Services\OfficeCoffeeFaqService;
use Inertia\Inertia;
use Inertia\Response;

class OfficeCoffeeController extends Controller
{
    public function __construct(private OfficeCoffeeFaqService $faqs) {}

    public function index(): Response
    {
        $page = OfficeCoffeePageData::all();
        $page['faqs']['items'] = $this->faqs->listForFrontend();

        return Inertia::render('frontend/office-coffee', $page);
    }
}
