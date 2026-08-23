<?php

namespace Database\Factories;

use App\Models\SyncLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class SyncLogFactory extends Factory
{
    protected $model = SyncLog::class;

    public function definition(): array
    {
        return [
            'status' => fake()->randomElement(['running', 'completed', 'failed']),
            'started_at' => fake()->dateTimeThisMonth(),
            'finished_at' => fake()->optional(0.7)->dateTimeThisMonth(),
            'locations_count' => fake()->numberBetween(0, 126),
            'episodes_count' => fake()->numberBetween(0, 51),
            'characters_count' => fake()->numberBetween(0, 826),
            'error_message' => null,
        ];
    }

    public function running(): static
    {
        return $this->state(fn () => [
            'status' => 'running',
            'finished_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'locations_count' => 126,
            'episodes_count' => 51,
            'characters_count' => 826,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
            'error_message' => 'Connection failed',
        ]);
    }
}
