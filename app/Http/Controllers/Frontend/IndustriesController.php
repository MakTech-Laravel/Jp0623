<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\IndustriesPageData;
use App\Http\Controllers\Controller;
use App\Services\LocationTypeService;
use App\Services\MachineService;
use Inertia\Inertia;
use Inertia\Response;

class IndustriesController extends Controller
{
    public function __construct(
        private LocationTypeService $locationTypes,
        private MachineService $machines,
    ) {}

    public function index(): Response
    {
        return Inertia::render(
            'frontend/industries',
            IndustriesPageData::index($this->locationTypes->listIndustries()),
        );
    }

    public function show(string $industry): Response
    {
        $locationType = $this->locationTypes->findIndustryBySlug($industry);

        abort_if($locationType === null, 404);

        return Inertia::render(
            'frontend/industries/show',
            IndustriesPageData::show($locationType, $this->machines->listForFrontend()),
        );
    }
}
