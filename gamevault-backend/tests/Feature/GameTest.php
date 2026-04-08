<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    private function makeGame(array $overrides = []): Game
    {
        return Game::create(array_merge([
            'title'        => 'Test Game ' . uniqid(),
            'description'  => 'Una descripción suficientemente larga para pasar la validación mínima del campo.',
            'developer'    => 'Dev Studio',
            'publisher'    => 'Publisher Co',
            'price'        => 29.99,
            'genres'       => ['Action', 'RPG'],
            'platforms'    => ['Windows'],
            'release_date' => '2024-01-15',
            'is_active'    => true,
        ], $overrides));
    }

    // ── Listado público ───────────────────────────────────────────────────────

    public function test_anyone_can_list_games(): void
    {
        $this->makeGame();
        $this->makeGame();

        $response = $this->getJson('/api/games');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'total', 'per_page', 'current_page']);
    }

    public function test_inactive_games_are_hidden_from_public(): void
    {
        $this->makeGame(['is_active' => false]);

        $response = $this->getJson('/api/games');

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('total'));
    }

    public function test_games_can_be_filtered_by_search(): void
    {
        $this->makeGame(['title' => 'Super Mario Bros']);
        $this->makeGame(['title' => 'Zelda Breath']);

        $response = $this->getJson('/api/games?search=Mario');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total'));
    }

    // ── Detalle ───────────────────────────────────────────────────────────────

    public function test_anyone_can_view_game_detail(): void
    {
        $game = $this->makeGame();

        $response = $this->getJson("/api/games/{$game->id}");

        $response->assertStatus(200)
                 ->assertJson(['id' => $game->id, 'title' => $game->title]);
    }

    // ── CRUD admin ────────────────────────────────────────────────────────────

    public function test_admin_can_create_game(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/games', [
            'title'        => 'New Game',
            'description'  => 'Una descripción suficientemente larga para pasar la validación mínima del campo.',
            'developer'    => 'Studio X',
            'publisher'    => 'Pub Y',
            'price'        => 49.99,
            'genres'       => ['Action'],
            'platforms'    => ['Windows', 'PlayStation'],
            'release_date' => '2025-06-01',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'New Game']);

        $this->assertDatabaseHas('games', ['title' => 'New Game']);
    }

    public function test_regular_user_cannot_create_game(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/games', [
            'title' => 'Hacked Game',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_game(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $game  = $this->makeGame();

        $response = $this->actingAs($admin)->putJson("/api/games/{$game->id}", [
            'price' => 19.99,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['price' => '19.99']);
    }

    public function test_admin_can_delete_game(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $game  = $this->makeGame();

        $response = $this->actingAs($admin)->deleteJson("/api/games/{$game->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('games', ['id' => $game->id]);
    }
}
