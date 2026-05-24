<?php

namespace Database\Seeders;

use App\Models\PopulationRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['country' => 'United States', 'year' => 2020, 'total_population' => 331002651, 'population_growth' => 0.4],
            ['country' => 'United States', 'year' => 2021, 'total_population' => 332915073, 'population_growth' => 0.6],
            ['country' => 'United States', 'year' => 2022, 'total_population' => 334805269, 'population_growth' => 0.6],
            ['country' => 'India',         'year' => 2020, 'total_population' => 1380004385, 'population_growth' => 0.99],
            ['country' => 'India',         'year' => 2021, 'total_population' => 1393409038, 'population_growth' => 0.97],
            ['country' => 'India',         'year' => 2022, 'total_population' => 1406631776, 'population_growth' => 0.95],
            ['country' => 'Germany',       'year' => 2020, 'total_population' => 83783942,  'population_growth' => 0.22],
            ['country' => 'Germany',       'year' => 2021, 'total_population' => 83900473,  'population_growth' => 0.14],
            ['country' => 'Germany',       'year' => 2022, 'total_population' => 84079811,  'population_growth' => 0.21],
        ];

        foreach ($records as $record) {
            PopulationRecord::updateOrCreate(
                ['country' => $record['country'], 'year' => $record['year']],
                array_merge($record, ['extracted_at' => Carbon::now()])
            );
        }

        $this->command->info('Seeded ' . count($records) . ' population records.');
    }
}