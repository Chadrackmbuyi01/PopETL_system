<?php

use App\Http\Controllers\Api\PopulationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('population')->group(function () {
    // GET /api/population — paginated list of all records
    Route::get('/', [PopulationController::class, 'index']);

    // GET /api/population/countries — distinct country list
    Route::get('/countries', [PopulationController::class, 'countries']);

    // POST /api/population/fetch — trigger ETL for a country
    Route::post('/fetch', [PopulationController::class, 'fetch']);

    // DELETE /api/population/{id} — remove a record
    Route::delete('/{id}', [PopulationController::class, 'destroy']);
});