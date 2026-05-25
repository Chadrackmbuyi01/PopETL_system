<?php

namespace Tests\Feature;

use App\Models\PopulationRecord;
use App\Services\PopulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PopulationApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: GET /api/population returns a list of records.
     */
    public function test_can_list_population_records(): void
    {
        PopulationRecord::create([
            'country' => 'France',
            'year' => 2024,
            'total_population' => 68000000,
            'extracted_at' => now(),
        ]);

        $response = $this->getJson('/api/population');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.country', 'France');
    }

    /**
     * Test: POST /api/population/fetch triggers ETL and returns success.
     */
    public function test_can_trigger_fetch_via_api(): void
    {
        // Mock the service to avoid real API calls
        $mock = $this->mock(PopulationService::class, function ($mock) {
            $mock->shouldReceive('runEtl')
                ->once()
                ->with('Italy', null)
                ->andReturn(['stored' => 1]);
        });

        $response = $this->postJson('/api/population/fetch', [
            'country' => 'Italy'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['stored' => 1]);
    }

    /**
     * Test: DELETE /api/population/{id} removes a record.
     */
    public function test_can_delete_a_record(): void
    {
        $record = PopulationRecord::create([
            'country' => 'Spain',
            'year' => 2024,
            'total_population' => 47000000,
            'extracted_at' => now(),
        ]);

        $response = $this->deleteJson("/api/population/{$record->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('population_record', ['id' => $record->id]);
    }
}
