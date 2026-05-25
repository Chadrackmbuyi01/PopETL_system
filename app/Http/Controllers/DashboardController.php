<?php

namespace App\Http\Controllers;

use App\Models\PopulationRecord;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'initialRecords' => PopulationRecord::latestFirst()->paginate(20),
            'initialCountries' => PopulationRecord::getUniqueCountries(),
        ]);
    }
}