<?php

namespace Database\Factories;

use App\Enums\CharacterGender;
use App\Enums\CharacterStatus;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class CharacterFactory extends Factory
{
    protected $model = Character::class;

    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->numberBetween(1, 10000),
            'name' => fake()->name(),
            'status' => fake()->randomElement(CharacterStatus::cases())->value,
            'species' => fake()->randomElement(['Human', 'Alien', 'Robot', 'Humanoid']),
            'type' => fake()->optional()->word(),
            'gender' => fake()->randomElement(CharacterGender::cases())->value,
            'image' => fake()->imageUrl(300, 300, 'character'),
            'origin_location_id' => Location::factory(),
            'current_location_id' => Location::factory(),
        ];
    }

    public function withEpisodes(int $count = 3): static
    {
        return $this->afterCreating(function (Character $character) use ($count) {
            $character->episodes()->attach(
                Episode::factory()->count($count)->create()->pluck('id')
            );
        });
    }
}
