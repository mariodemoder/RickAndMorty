<?php

namespace Tests\Feature\Api;

use App\Models\Character;
use App\Models\CharacterFavorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_list_favorites(): void
    {
        $response = $this->getJson('/api/favorites');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_favorites(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create();

        CharacterFavorite::create([
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/favorites');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_only_sees_own_favorites(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $character1 = Character::factory()->create();
        $character2 = Character::factory()->create();

        CharacterFavorite::create(['user_id' => $user1->id, 'character_id' => $character1->id]);
        CharacterFavorite::create(['user_id' => $user2->id, 'character_id' => $character2->id]);

        $token = $user1->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/favorites');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_unauthenticated_user_cannot_add_favorite(): void
    {
        $character = Character::factory()->create();

        $response = $this->postJson('/api/favorites', [
            'character_id' => $character->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_add_favorite(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/favorites', [
                'character_id' => $character->id,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Favorite added successfully.',
            ]);

        $this->assertDatabaseHas('character_favorites', [
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);
    }

    public function test_adding_duplicate_favorite_does_not_create_new_record(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        CharacterFavorite::create([
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/favorites', [
                'character_id' => $character->id,
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseCount('character_favorites', 1);
    }

    public function test_cannot_add_favorite_with_invalid_character(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/favorites', [
                'character_id' => 9999,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['character_id']);
    }

    public function test_unauthenticated_user_cannot_remove_favorite(): void
    {
        $response = $this->deleteJson('/api/favorites/1');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_remove_favorite(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        CharacterFavorite::create([
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/favorites/{$character->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Favorite removed successfully.',
            ]);

        $this->assertDatabaseMissing('character_favorites', [
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);
    }

    public function test_returns_404_when_removing_nonexistent_favorite(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/favorites/9999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => [
                    'message' => 'Favorite not found.',
                    'status' => 404,
                ],
            ]);
    }

    public function test_cannot_remove_other_users_favorite(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $character = Character::factory()->create();

        CharacterFavorite::create([
            'user_id' => $user2->id,
            'character_id' => $character->id,
        ]);

        $token = $user1->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/favorites/{$character->id}");

        $response->assertStatus(404);

        $this->assertDatabaseHas('character_favorites', [
            'user_id' => $user2->id,
            'character_id' => $character->id,
        ]);
    }
}
