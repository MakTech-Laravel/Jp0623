<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\ProductsPageData;
use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Inertia\Inertia;
use Inertia\Response;

class ProductsController extends Controller
{
    public function __construct(private ProductService $products) {}

    public function index(): Response
    {
        return Inertia::render('frontend/products', [
            'site' => ProductsPageData::site(),
            'hero' => ProductsPageData::hero(),
            'catalog' => $this->products->catalogForFrontend(),
        ]);
    }
}
