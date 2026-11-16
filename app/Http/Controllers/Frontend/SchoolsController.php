<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\SchoolsPageData;
use App\Http\Controllers\Controller;
use App\Services\ArticleService;
use App\Services\ProductService;
use Inertia\Inertia;
use Inertia\Response;

class SchoolsController extends Controller
{
    /**
     * Product kinds shown in "School-friendly picks".
     *
     * @var list<string>
     */
    private const PRODUCT_KINDS = ['healthy', 'nuts', 'drink', 'food'];

    public function __construct(
        private ProductService $products,
        private ArticleService $articles,
    ) {}

    public function index(): Response
    {
        $page = SchoolsPageData::all();
        $page['products']['items'] = array_map(
            fn (array $product): array => [
                'title' => $product['name'],
                'image' => $product['image'] ?? '',
            ],
            $this->products->listForKinds(self::PRODUCT_KINDS),
        );

        $articles = $this->articles->listForFrontend(3);
        $page['guide']['item'] = $articles[2] ?? $articles[0] ?? null;

        return Inertia::render('frontend/schools', $page);
    }
}
