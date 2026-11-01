<?php

namespace App\Http\Controllers\Frontend;

use App\Demo\MachineDetailPageData;
use App\Demo\MachinesPageData;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class MachinesController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('frontend/machines', MachinesPageData::all());
    }

    public function show(string $machine): Response
    {
        $page = MachineDetailPageData::all($machine);

        abort_if($page === null, 404);

        return Inertia::render('frontend/machines/show', $page);
    }
}
