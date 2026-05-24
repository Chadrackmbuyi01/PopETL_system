<?php

namespace App\Http\Controllers;

use App\Models\PopulationRecord;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $records = PopulationRecord::latestFirst()->paginate(20);

        $countries = PopulationRecord::select('country')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        return Inertia::render('Dashboard', [
            'initialRecords' => $records,
            'initialCountries' => $countries,
        ]);
    }
}