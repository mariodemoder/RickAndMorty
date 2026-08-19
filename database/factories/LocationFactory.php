<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->numberBetween(1, 10000),
            'name' => fake()->city(),
            'type' => fake()->randomElement(['Planet', 'Space station', 'Dimension', 'Unknown']),
            'dimension' => fake()->randomElement(['Dimension C-137', 'Dimension C-500A', 'unknown']),
        ];
    }
}
