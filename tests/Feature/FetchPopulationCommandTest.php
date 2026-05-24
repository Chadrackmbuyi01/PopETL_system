<?php

namespace Tests\Feature;

use App\Models\PopulationRecord;
use App\Services\PopulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class FetchPopulationCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: running the artisan command inserts a record into PostgreSQL.
     */
    public function test_fetch_command_stores_population_record(): void
    {
        // Mock the service so we don't hit the real API
        $mockService = Mockery::mock(PopulationService::class);
        $mockService->shouldReceive('runEtl')
            ->once()
            ->with('United States', null)
            ->andReturn([
                'stored'  => 1,
                'records' => collect([[
                    'country'            => 'United States',
                    'year'               => 2023,
                    'total_population'   => 334000000,
                    'population_growth'  => 0.4,
                    'population_density' => null,
                    'male_population'    => null,
                    'female_population'  => null,
                    'extracted_at'       => Carbon::now(),
                ]]),
            ]);

        $this->app->instance(PopulationService::class, $mockService);

        // Seed a real record via the service's storeBatch for assertion
        PopulationRecord::create([
            'country'          => 'United States',
            'year'             => 2023,
            'total_population' => 334000000,
            'extracted_at'     => Carbon::now(),
        ]);

        $this->artisan('population:fetch', ['country' => 'United States'])
            ->assertExitCode(0);

        $this->assertDatabaseHas('population_records', [
            'country' => 'United States',
            'year'    => 2023,
        ]);
    }

    /**
     * Test: POST /api/population/fetch returns 422 for invalid country.
     */
    public function test_fetch_api_validates_country(): void
    {
        $response = $this->postJson('/api/population/fetch', [
            'country' => '', // empty — should fail
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country']);
    }

    /**
     * Test: POST /api/population/fetch returns 422 for out-of-range year.
     */
    public function test_fetch_api_validates_year_range(): void
    {
        $response = $this->postJson('/api/population/fetch', [
            'country' => 'Germany',
            'year'    => 1800, // before 1900
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['year']);
    }

    /**
     * Test: GET /api/population returns paginated records.
     */
    public function test_index_returns_paginated_records(): void
    {
        PopulationRecord::factory()->count(5)->create();

        $response = $this->getJson('/api/population');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data'          => [['id', 'country', 'year', 'total_population', 'extracted_at']],
                'current_page',
                'last_page',
                'total',
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}