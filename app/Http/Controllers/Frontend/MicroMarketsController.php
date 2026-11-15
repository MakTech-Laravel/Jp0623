<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\MicroMarketsPageData;
use App\Http\Controllers\Controller;
use App\Services\MachineService;
use App\Services\ProductService;
use Inertia\Inertia;
use Inertia\Response;

class MicroMarketsController extends Controller
{
    /** Product kinds shown in "From breakfast to late shift." */
    private const OFFERING_KINDS = ['food', 'drink', 'healthy', 'pastry', 'coffee'];

    /** Machine slugs shown in "Micro markets & Vendera coolers: we offer both." */
    private const COMPARE_MACHINE_SLUGS = ['micro-market', 'ai-cooler'];

    public function __construct(
        private ProductService $products,
        private MachineService $machines,
    ) {}

    public function index(): Response
    {
        $page = MicroMarketsPageData::all();
        $page['offerings']['items'] = $this->products->listForKinds(self::OFFERING_KINDS);
        $page['compare']['items'] = $this->machines->compareCardsForFrontend(self::COMPARE_MACHINE_SLUGS);

        return Inertia::render('frontend/micro-markets', $page);
    }
}
