<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\HomePageData;
use App\Http\Controllers\Controller;
use App\Models\LocationType;
use App\Services\HomeFaqService;
use App\Services\LocationTypeService;
use App\Services\MachineService;
use App\Services\ProductService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private HomeFaqService $faqs,
        private LocationTypeService $locationTypes,
        private MachineService $machines,
        private ProductService $products,
    ) {}

    public function index(): Response
    {
        $page = HomePageData::all();
        $page['faqs']['items'] = $this->faqs->listForFrontend();
        $page['requestForm']['locationTypes'] = $this->locationTypes->listActiveNamesForFrontend();
        $page['featuredMachines']['items'] = $this->machines->listFeaturedForFrontend();
        $page['featuredMachines']['link']['label'] = 'See all '.$this->machines->countActive().' machines →';
        $page['products']['items'] = $this->products->listFeaturedForFrontend();
        $page['industries']['items'] = array_map(
            fn (LocationType $industry): array => [
                'slug' => (string) $industry->slug,
                'label' => $industry->name,
                'href' => '/industries/'.$industry->slug,
            ],
            $this->locationTypes->listIndustries(),
        );

        return Inertia::render('frontend/home', $page);
    }
}
