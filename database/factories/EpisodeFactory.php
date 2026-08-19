<?php

namespace Database\Factories;

use App\Models\Episode;
use Illuminate\Database\Eloquent\Factories\Factory;

class EpisodeFactory extends Factory
{
    protected $model = Episode::class;

    public function definition(): array
    {
        $season = fake()->numberBetween(1, 10);
        $episode = fake()->numberBetween(1, 30);

        return [
            'external_id' => fake()->unique()->numberBetween(1, 10000),
            'name' => fake()->words(3, true),
            'air_date' => fake()->dateTimeThisDecade()->format('F j, Y'),
            'episode_code' => sprintf('S%02dE%02d', $season, $episode),
        ];
    }
}
