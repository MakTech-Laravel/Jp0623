<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\ProductsPageData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ProductsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('frontend/products', ProductsPageData::all());
    }
}
