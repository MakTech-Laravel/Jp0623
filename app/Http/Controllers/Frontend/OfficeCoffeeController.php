<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\OfficeCoffeePageData;
use App\Http\Controllers\Controller;
use App\Services\CoffeeMachineService;
use App\Services\CoffeeTypeService;
use App\Services\OfficeCoffeeFaqService;
use Inertia\Inertia;
use Inertia\Response;

class OfficeCoffeeController extends Controller
{
    public function __construct(
        private OfficeCoffeeFaqService $faqs,
        private CoffeeMachineService $coffeeMachines,
        private CoffeeTypeService $coffeeTypes,
    ) {}

    public function index(): Response
    {
        $page = OfficeCoffeePageData::all();
        $page['machines']['items'] = $this->coffeeMachines->listForFrontend();
        $page['types']['items'] = $this->coffeeTypes->listForFrontend();
        $page['faqs']['items'] = $this->faqs->listForFrontend();

        return Inertia::render('frontend/office-coffee', $page);
    }
}
