<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\ResourcesPageData;
use App\Http\Controllers\Controller;
use App\Services\ArticleService;
use Inertia\Inertia;
use Inertia\Response;

class ResourcesController extends Controller
{
    public function __construct(private ArticleService $articles) {}

    public function index(): Response
    {
        $page = ResourcesPageData::all();
        $page['articles']['items'] = $this->articles->listForFrontend();

        return Inertia::render('frontend/resources', $page);
    }

    public function show(string $article): Response
    {
        $detail = $this->articles->detailForFrontend($article);

        abort_if($detail === null, 404);

        return Inertia::render('frontend/resources/show', array_merge(
            ['site' => ResourcesPageData::site()],
            $detail,
        ));
    }
}
