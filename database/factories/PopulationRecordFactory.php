<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PopulationRecordFactory extends Factory
{
    public function definition(): array
    {
        static $year = 1990;

        return [
            'country'            => $this->faker->country(),
            'year'               => $year++,
            'total_population'   => $this->faker->numberBetween(1_000_000, 2_000_000_000),
            'population_growth'  => $this->faker->randomFloat(4, -2, 5),
            'population_density' => $this->faker->randomFloat(2, 1, 500),
            'male_population'    => $this->faker->numberBetween(500_000, 1_000_000_000),
            'female_population'  => $this->faker->numberBetween(500_000, 1_000_000_000),
            'extracted_at'       => now(),
        ];
    }
}