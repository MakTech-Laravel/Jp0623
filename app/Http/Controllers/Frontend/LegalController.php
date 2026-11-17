<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\HomePageData;
use App\Http\Controllers\Controller;
use App\Services\LegalPageService;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function __construct(private LegalPageService $legalPages) {}

    public function show(string $page): Response
    {
        $legalPage = $this->legalPages->findBySlug($page);

        abort_if($legalPage === null, 404);

        return Inertia::render('frontend/legal', [
            'site' => HomePageData::site(),
            'page' => $legalPage->slug,
            ...$this->legalPages->toFrontendPayload($legalPage),
        ]);
    }
}
