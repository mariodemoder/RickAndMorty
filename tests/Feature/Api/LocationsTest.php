<?php

namespace Tests\Feature\Api;

use App\Models\Character;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_locations(): void
    {
        Location::factory()->count(5)->create();

        $response = $this->getJson('/api/locations');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'external_id', 'name', 'type', 'dimension'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_can_get_location_detail(): void
    {
        $location = Location::factory()->create([
            'name' => 'Earth',
            'type' => 'Planet',
            'dimension' => 'Dimension C-137',
        ]);

        $response = $this->getJson("/api/locations/{$location->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $location->id,
                    'name' => 'Earth',
                    'type' => 'Planet',
                    'dimension' => 'Dimension C-137',
                ],
            ]);
    }

    public function test_returns_404_for_nonexistent_location(): void
    {
        $response = $this->getJson('/api/locations/9999');

        $response->assertStatus(404);
    }

    public function test_can_filter_locations_by_name(): void
    {
        Location::factory()->create(['name' => 'Earth']);
        Location::factory()->create(['name' => 'Mars']);
        Location::factory()->create(['name' => 'Earth Dimension C-137']);

        $response = $this->getJson('/api/locations?name=earth');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_locations_by_type(): void
    {
        Location::factory()->create(['type' => 'Planet']);
        Location::factory()->create(['type' => 'Planet']);
        Location::factory()->create(['type' => 'Dimension']);

        $response = $this->getJson('/api/locations?type=Planet');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_locations_by_dimension(): void
    {
        Location::factory()->create(['dimension' => 'Dimension C-137']);
        Location::factory()->create(['dimension' => 'Dimension C-137']);
        Location::factory()->create(['dimension' => 'Dimension D-999']);

        $response = $this->getJson('/api/locations?dimension=C-137');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_by_multiple_criteria(): void
    {
        Location::factory()->create(['name' => 'Earth', 'type' => 'Planet', 'dimension' => 'Dimension C-137']);
        Location::factory()->create(['name' => 'Earth', 'type' => 'Dimension', 'dimension' => 'Dimension C-137']);
        Location::factory()->create(['name' => 'Mars', 'type' => 'Planet', 'dimension' => 'Dimension C-137']);

        $response = $this->getJson('/api/locations?name=earth&type=Planet');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_pagination_works_correctly(): void
    {
        Location::factory()->count(25)->create();

        $response = $this->getJson('/api/locations');

        $response->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_location_detail_includes_residents(): void
    {
        $location = Location::factory()->create();
        Character::factory()->count(3)->create([
            'current_location_id' => $location->id,
        ]);

        $response = $this->getJson("/api/locations/{$location->id}");

        $response->assertOk()
            ->assertJsonCount(3, 'data.residents');
    }
}
