<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\MachineDetailPageData;
use App\Demo\MachinesPageData;
use App\Http\Controllers\Controller;
use App\Services\MachineFaqService;
use App\Services\MachineService;
use Inertia\Inertia;
use Inertia\Response;

class MachinesController extends Controller
{
    public function __construct(
        private MachineService $machines,
        private MachineFaqService $faqs,
    ) {}

    public function index(): Response
    {
        $page = MachinesPageData::all();
        $page['lineup']['filters'] = $this->machines->lineupFilters();
        $page['lineup']['items'] = $this->machines->listForFrontend();
        $page['faqs']['items'] = $this->faqs->listForFrontend();

        return Inertia::render('frontend/machines', $page);
    }

    public function show(string $machine): Response
    {
        $detail = $this->machines->detailForFrontend($machine);

        abort_if($detail === null, 404);

        return Inertia::render('frontend/machines/show', array_merge(
            [
                'site' => MachinesPageData::site(),
                'checks' => MachineDetailPageData::checks(),
                'cta' => MachineDetailPageData::cta(),
            ],
            $detail,
        ));
    }
}
