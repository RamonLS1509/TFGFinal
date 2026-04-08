<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    private function makeGame(): Game
    {
        return Game::create([
            'title'        => 'Game ' . uniqid(),
            'description'  => 'Una descripción suficientemente larga para pasar la validación mínima del campo.',
            'developer'    => 'Dev',
            'publisher'    => 'Pub',
            'price'        => 9.99,
            'genres'       => ['Indie'],
            'platforms'    => ['Windows'],
            'release_date' => '2024-01-01',
        ]);
    }

    public function test_user_can_view_wishlist(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();
        Wishlist::create(['user_id' => $user->id, 'game_id' => $game->id]);

        $response = $this->actingAs($user)->getJson('/api/wishlist');

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    public function test_user_can_add_game_to_wishlist(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();

        $response = $this->actingAs($user)->postJson('/api/wishlist', [
            'game_id'  => $game->id,
            'priority' => 1,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['game_id' => $game->id, 'priority' => 1]);

        $this->assertDatabaseHas('wishlists', [
            'user_id'  => $user->id,
            'game_id'  => $game->id,
            'priority' => 1,
        ]);
    }

    public function test_user_cannot_add_duplicate_to_wishlist(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();
        Wishlist::create(['user_id' => $user->id, 'game_id' => $game->id]);

        $response = $this->actingAs($user)->postJson('/api/wishlist', [
            'game_id' => $game->id,
        ]);

        $response->assertStatus(409);
    }

    public function test_user_can_remove_game_from_wishlist(): void
    {
        $user  = User::factory()->create();
        $game  = $this->makeGame();
        $entry = Wishlist::create(['user_id' => $user->id, 'game_id' => $game->id]);

        $response = $this->actingAs($user)->deleteJson("/api/wishlist/{$entry->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('wishlists', ['id' => $entry->id]);
    }

    public function test_user_can_update_wishlist_priority(): void
    {
        $user  = User::factory()->create();
        $game  = $this->makeGame();
        $entry = Wishlist::create(['user_id' => $user->id, 'game_id' => $game->id, 'priority' => 0]);

        $response = $this->actingAs($user)->putJson("/api/wishlist/{$entry->id}", [
            'priority' => 1,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['priority' => 1]);
    }

    public function test_user_cannot_modify_another_users_wishlist(): void
    {
        $owner  = User::factory()->create();
        $hacker = User::factory()->create();
        $game   = $this->makeGame();
        $entry  = Wishlist::create(['user_id' => $owner->id, 'game_id' => $game->id]);

        $response = $this->actingAs($hacker)->deleteJson("/api/wishlist/{$entry->id}");

        $response->assertStatus(403);
    }
}
