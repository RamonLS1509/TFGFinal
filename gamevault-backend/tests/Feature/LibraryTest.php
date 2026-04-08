<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Library;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryTest extends TestCase
{
    use RefreshDatabase;

    private function makeGame(): Game
    {
        return Game::create([
            'title'        => 'Game ' . uniqid(),
            'description'  => 'Una descripción suficientemente larga para pasar la validación mínima del campo.',
            'developer'    => 'Dev',
            'publisher'    => 'Pub',
            'price'        => 19.99,
            'genres'       => ['Action'],
            'platforms'    => ['Windows'],
            'release_date' => '2024-01-01',
        ]);
    }

    public function test_user_can_view_their_library(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();
        Library::create([
            'user_id'    => $user->id,
            'game_id'    => $game->id,
            'price_paid' => $game->price,
        ]);

        $response = $this->actingAs($user)->getJson('/api/library');

        $response->assertStatus(200)
                 ->assertJsonCount(1)
                 ->assertJsonFragment(['game_id' => $game->id]);
    }

    public function test_unauthenticated_user_cannot_view_library(): void
    {
        $response = $this->getJson('/api/library');
        $response->assertStatus(401);
    }

    public function test_user_can_add_game_to_library(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();

        $response = $this->actingAs($user)->postJson('/api/library', [
            'game_id' => $game->id,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['game_id' => $game->id]);

        $this->assertDatabaseHas('libraries', [
            'user_id' => $user->id,
            'game_id' => $game->id,
        ]);
    }

    public function test_user_cannot_add_same_game_twice(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();
        Library::create(['user_id' => $user->id, 'game_id' => $game->id, 'price_paid' => 0]);

        $response = $this->actingAs($user)->postJson('/api/library', [
            'game_id' => $game->id,
        ]);

        $response->assertStatus(409);
    }

    public function test_user_can_remove_game_from_library(): void
    {
        $user  = User::factory()->create();
        $game  = $this->makeGame();
        $entry = Library::create([
            'user_id'    => $user->id,
            'game_id'    => $game->id,
            'price_paid' => 0,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/library/{$entry->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('libraries', ['id' => $entry->id]);
    }

    public function test_user_cannot_delete_another_users_library_entry(): void
    {
        $owner  = User::factory()->create();
        $hacker = User::factory()->create();
        $game   = $this->makeGame();
        $entry  = Library::create([
            'user_id'    => $owner->id,
            'game_id'    => $game->id,
            'price_paid' => 0,
        ]);

        $response = $this->actingAs($hacker)->deleteJson("/api/library/{$entry->id}");

        $response->assertStatus(403);
    }
}
