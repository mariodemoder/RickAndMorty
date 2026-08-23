<?php

namespace Tests\Feature\Api;

use App\Models\Character;
use App\Models\Episode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EpisodesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_episodes(): void
    {
        Episode::factory()->count(5)->create();

        $response = $this->getJson('/api/episodes');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'external_id', 'name', 'air_date', 'episode_code'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_can_get_episode_detail(): void
    {
        $episode = Episode::factory()->create([
            'name' => 'Pilot',
            'episode_code' => 'S01E01',
        ]);

        $response = $this->getJson("/api/episodes/{$episode->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $episode->id,
                    'name' => 'Pilot',
                    'episode_code' => 'S01E01',
                ],
            ]);
    }

    public function test_returns_404_for_nonexistent_episode(): void
    {
        $response = $this->getJson('/api/episodes/9999');

        $response->assertStatus(404);
    }

    public function test_can_filter_episodes_by_name(): void
    {
        Episode::factory()->create(['name' => 'Pilot']);
        Episode::factory()->create(['name' => 'Lawnmower Dog']);
        Episode::factory()->create(['name' => 'Anatomy Park']);

        $response = $this->getJson('/api/episodes?name=pilot');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_episodes_by_episode_code(): void
    {
        Episode::factory()->create(['episode_code' => 'S01E01']);
        Episode::factory()->create(['episode_code' => 'S01E02']);
        Episode::factory()->create(['episode_code' => 'S02E01']);

        $response = $this->getJson('/api/episodes?episode=S01');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_by_name_and_episode_code(): void
    {
        Episode::factory()->create(['name' => 'Pilot', 'episode_code' => 'S01E01']);
        Episode::factory()->create(['name' => 'Pilot', 'episode_code' => 'S02E01']);
        Episode::factory()->create(['name' => 'Lawnmower Dog', 'episode_code' => 'S01E02']);

        $response = $this->getJson('/api/episodes?name=pilot&episode=S01');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_pagination_works_correctly(): void
    {
        Episode::factory()->count(25)->create();

        $response = $this->getJson('/api/episodes');

        $response->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_episode_detail_includes_characters(): void
    {
        $episode = Episode::factory()->create();
        $characters = Character::factory()->count(3)->create();
        $episode->characters()->attach($characters->pluck('id'));

        $response = $this->getJson("/api/episodes/{$episode->id}");

        $response->assertOk()
            ->assertJsonCount(3, 'data.characters');
    }
}
