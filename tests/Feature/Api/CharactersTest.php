<?php

namespace Tests\Feature\Api;

use App\Models\Character;
use App\Models\Episode;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharactersTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_characters(): void
    {
        Character::factory()->count(5)->create();

        $response = $this->getJson('/api/characters');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'external_id', 'name', 'status', 'species', 'gender', 'image'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_can_get_character_detail(): void
    {
        $character = Character::factory()->create([
            'name' => 'Rick Sanchez',
            'status' => 'Alive',
        ]);

        $response = $this->getJson("/api/characters/{$character->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $character->id,
                    'name' => 'Rick Sanchez',
                    'status' => 'Alive',
                ],
            ]);
    }

    public function test_returns_404_for_nonexistent_character(): void
    {
        $response = $this->getJson('/api/characters/9999');

        $response->assertStatus(404);
    }

    public function test_can_filter_characters_by_name(): void
    {
        Character::factory()->create(['name' => 'Rick Sanchez']);
        Character::factory()->create(['name' => 'Morty Smith']);
        Character::factory()->create(['name' => 'Summer Smith']);

        $response = $this->getJson('/api/characters?name=rick');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_characters_by_status(): void
    {
        Character::factory()->create(['status' => 'Alive']);
        Character::factory()->create(['status' => 'Alive']);
        Character::factory()->create(['status' => 'Dead']);

        $response = $this->getJson('/api/characters?status=Alive');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_characters_by_species(): void
    {
        Character::factory()->create(['species' => 'Human']);
        Character::factory()->create(['species' => 'Alien']);
        Character::factory()->create(['species' => 'Human']);

        $response = $this->getJson('/api/characters?species=Human');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_characters_by_gender(): void
    {
        Character::factory()->create(['gender' => 'Male']);
        Character::factory()->create(['gender' => 'Female']);
        Character::factory()->create(['gender' => 'Male']);

        $response = $this->getJson('/api/characters?gender=Male');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_characters_by_multiple_criteria(): void
    {
        Character::factory()->create(['name' => 'Rick', 'status' => 'Alive', 'gender' => 'Male']);
        Character::factory()->create(['name' => 'Rick', 'status' => 'Dead', 'gender' => 'Male']);
        Character::factory()->create(['name' => 'Morty', 'status' => 'Alive', 'gender' => 'Male']);

        $response = $this->getJson('/api/characters?name=rick&status=Alive');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_pagination_works_correctly(): void
    {
        Character::factory()->count(25)->create();

        $response = $this->getJson('/api/characters');

        $response->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_can_get_second_page(): void
    {
        Character::factory()->count(25)->create();

        $response = $this->getJson('/api/characters?page=2');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }

    public function test_character_detail_includes_locations(): void
    {
        $origin = Location::factory()->create(['name' => 'Earth']);
        $current = Location::factory()->create(['name' => 'Citadel']);

        $character = Character::factory()->create([
            'origin_location_id' => $origin->id,
            'current_location_id' => $current->id,
        ]);

        $response = $this->getJson("/api/characters/{$character->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'origin',
                    'location',
                ],
            ]);
    }

    public function test_character_detail_includes_episodes(): void
    {
        $character = Character::factory()->create();
        $episodes = Episode::factory()->count(3)->create();
        $character->episodes()->attach($episodes->pluck('id'));

        $response = $this->getJson("/api/characters/{$character->id}");

        $response->assertOk()
            ->assertJsonCount(3, 'data.episodes');
    }
}
