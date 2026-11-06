<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\LegalPageData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LegalController extends Controller
{
    public function show(string $page): Response
    {
        if (! array_key_exists($page, LegalPageData::pages())) {
            throw new NotFoundHttpException;
        }

        return Inertia::render('frontend/legal', LegalPageData::for($page));
    }
}
