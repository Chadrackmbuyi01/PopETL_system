<?php

namespace App\Services;

use App\Models\PopulationRecord;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PopulationService
{
    private const API_BASE_URL = 'https://api.api-ninjas.com/v1/population';

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.api_ninjas.key', '');
    }

    public function fetchFromApi(string $country, ?int $year = null): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('API_NINJAS_KEY is not configured in .env');
        }

        $params = ['country' => $country];
        if ($year !== null) {
            $params['year'] = $year;
        }

        try {
            $response = Http::withHeaders(['X-Api-Key' => $this->apiKey])
                ->timeout(15)
                ->get(self::API_BASE_URL, $params);

            $response->throw();
            $data = $response->json();

            if (empty($data)) {
                Log::warning('API-Ninjas returned empty response', ['country' => $country, 'year' => $year]);
                return [];
            }

            // Extract records from historical_population and population_forecast
            $records = [];
            
            if (isset($data['historical_population'])) {
                $records = array_merge($records, $data['historical_population']);
            }
            
            if (isset($data['population_forecast'])) {
                $records = array_merge($records, $data['population_forecast']);
            }

            // If it's a direct array (fallback for different API structures)
            if (empty($records) && is_array($data)) {
                $records = isset($data[0]) ? $data : [$data];
            }

            // If a specific year was requested, filter the results
            if ($year !== null) {
                $records = array_filter($records, fn($r) => (int) ($r['year'] ?? 0) === (int) $year);
            }

            return array_values($records);

        } catch (RequestException $e) {
            Log::error('API-Ninjas request failed', [
                'country' => $country,
                'status'  => $e->response->status(),
                'body'    => $e->response->body(),
            ]);
            throw new RuntimeException(
                "API request failed (HTTP {$e->response->status()}): {$e->response->body()}"
            );
        }
    }

    public function transformData(array $rawRecords, string $requestedCountry): Collection
    {
        $extractedAt = Carbon::now();

        return collect($rawRecords)->map(function (array $record) use ($requestedCountry, $extractedAt): array {
            $country = $record['country'] ?? $requestedCountry;

            // Density: prefer API 'density' key, otherwise calculate if area is available
            $density = $record['density'] ?? $record['population_density'] ?? null;
            if ($density === null && !empty($record['area_km2']) && (int) $record['area_km2'] > 0) {
                $population = (int) ($record['population'] ?? $record['total_population'] ?? 0);
                $density    = round($population / (int) $record['area_km2'], 4);
            }

            return [
                'country'            => trim($country),
                'year'               => (int) ($record['year'] ?? date('Y')),
                'total_population'   => (int) ($record['population'] ?? $record['total_population'] ?? 0),
                'population_growth'  => isset($record['yearly_change_percentage']) 
                    ? round((float) $record['yearly_change_percentage'], 4)
                    : (isset($record['population_growth']) ? round((float) $record['population_growth'], 4) : null),
                'population_density' => $density !== null ? round((float) $density, 4) : null,
                'male_population'    => isset($record['male_population']) ? (int) $record['male_population'] : null,
                'female_population'  => isset($record['female_population']) ? (int) $record['female_population'] : null,
                'extracted_at'       => $extractedAt,
            ];
        });
    }

    public function storeBatch(Collection $records): int
    {
        $stored = 0;
        foreach ($records as $data) {
            PopulationRecord::updateOrCreate(
                ['country' => $data['country'], 'year' => $data['year']],
                $data
            );
            $stored++;
        }
        return $stored;
    }

    public function runEtl(string $country, ?int $year = null): array
    {
        $raw       = $this->fetchFromApi($country, $year);
        $transform = $this->transformData($raw, $country);
        $stored    = $this->storeBatch($transform);

        Log::info('ETL pipeline completed', [
            'country' => $country, 'year' => $year,
            'fetched' => count($raw), 'stored' => $stored,
        ]);

        return ['stored' => $stored, 'records' => $transform];
    }
}