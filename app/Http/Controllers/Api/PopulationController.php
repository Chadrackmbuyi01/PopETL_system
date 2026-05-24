<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FetchPopulationRequest;
use App\Models\PopulationRecord;
use App\Services\PopulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PopulationController extends Controller
{
    public function __construct(
        private readonly PopulationService $populationService
    ) {}

    /**
     * GET /api/population
     * Return all population records, paginated.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 20), 100);
        $country = $request->get('country');

        $query = PopulationRecord::latestFirst();

        if ($country) {
            $query->forCountry($country);
        }

        $records = $query->paginate($perPage);

        return response()->json($records);
    }

    /**
     * POST /api/population/fetch
     * Trigger ETL for a given country.
     */
    public function fetch(FetchPopulationRequest $request): JsonResponse
    {
        $country = $request->validated('country');
        $year    = $request->validated('year');

        try {
            $result = $this->populationService->runEtl($country, $year);

            if ($result['stored'] === 0) {
                return response()->json([
                    'message' => "No population data found for '{$country}'. Please check the country name and try again.",
                    'stored'  => 0,
                ], 404);
            }

            return response()->json([
                'message' => "Successfully fetched and saved {$result['stored']} record(s) for '{$country}'.",
                'stored'  => $result['stored'],
                'records' => $result['records']->values(),
            ], 201);

        } catch (RuntimeException $e) {
            return response()->json([
                'message' => 'ETL pipeline failed: ' . $e->getMessage(),
            ], 502);
        }
    }

    /**
     * GET /api/population/countries
     * Return distinct countries in the database.
     */
    public function countries(): JsonResponse
    {
        $countries = PopulationRecord::select('country')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        return response()->json($countries);
    }

    /**
     * DELETE /api/population/{id}
     * Remove a single record.
     */
    public function destroy(int $id): JsonResponse
    {
        $record = PopulationRecord::findOrFail($id);
        $record->delete();

        return response()->json(['message' => 'Record deleted successfully.']);
    }
}