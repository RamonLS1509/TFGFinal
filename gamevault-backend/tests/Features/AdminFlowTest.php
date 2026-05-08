<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeGame(array $overrides = []): Game
    {
        return Game::create(array_merge([
            'title'        => 'Game ' . uniqid(),
            'description'  => 'Descripción suficientemente larga para pasar la validación del campo requerido.',
            'developer'    => 'Dev Studio',
            'publisher'    => 'Publisher Co',
            'price'        => 29.99,
            'genres'       => ['Action'],
            'platforms'    => ['Windows'],
            'release_date' => '2024-01-15',
            'is_active'    => true,
        ], $overrides));
    }

    // ── TC-026: Búsqueda en API ───────────────────────────────────────────────

    public function test_TC026_api_search_returns_correct_results(): void
    {
        $this->makeGame(['title' => 'Elden Ring']);
        $this->makeGame(['title' => 'Cyberpunk 2077']);

        $response = $this->getJson('/api/v1/search?q=Elden');

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data', 'meta']);

        $this->assertEquals(1, $response->json('meta.results_count'));
        $this->assertEquals('Elden Ring', $response->json('data.0.title'));
    }

    public function test_TC026_api_search_without_query_returns_all(): void
    {
        $this->makeGame();
        $this->makeGame();

        $response = $this->getJson('/api/v1/search');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, $response->json('meta.results_count'));
    }

    public function test_TC026_api_search_filters_by_genre(): void
    {
        $this->makeGame(['genres' => ['RPG']]);
        $this->makeGame(['genres' => ['Action']]);

        $response = $this->getJson('/api/v1/search?genre=RPG');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.results_count'));
    }

    public function test_TC026_api_search_filters_by_price(): void
    {
        $this->makeGame(['title' => 'Cheap Game',     'price' => 5.00]);
        $this->makeGame(['title' => 'Expensive Game', 'price' => 60.00]);

        $response = $this->getJson('/api/v1/search?q=Game&max_price=10');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.results_count'));
        $this->assertEquals('Cheap Game', $response->json('data.0.title'));
    }

    // ── TC-027: Documentación API ─────────────────────────────────────────────

    public function test_TC027_api_stats_endpoint_works(): void
    {
        $response = $this->getJson('/api/v1/stats');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['platform' => ['total_users', 'total_games']],
                     'meta',
                 ]);
    }

    public function test_TC027_api_genres_endpoint_works(): void
    {
        $this->makeGame(['genres' => ['RPG', 'Action']]);

        $response = $this->getJson('/api/v1/genres');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data',
                     'meta' => ['total_genres'],
                 ]);
    }

    public function test_TC027_api_platforms_endpoint_works(): void
    {
        $this->makeGame(['platforms' => ['Windows', 'PlayStation']]);

        $response = $this->getJson('/api/v1/platforms');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data',
                     'meta' => ['total_platforms'],
                 ]);
    }

    public function test_TC027_api_game_by_slug_works(): void
    {
        $game = $this->makeGame(['title' => 'Elden Ring']);

        $response = $this->getJson("/api/v1/game/{$game->slug}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'title', 'slug', 'community'],
                 ]);
    }

    // ── TC-028: Crear juego ───────────────────────────────────────────────────

    public function test_TC028_admin_can_create_game(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->postJson('/api/games', [
            'title'        => 'Nuevo Juego Test',
            'description'  => 'Descripción suficientemente larga para pasar la validación del campo requerido.',
            'developer'    => 'Dev Studio',
            'publisher'    => 'Publisher Co',
            'price'        => 49.99,
            'genres'       => ['Action', 'RPG'],
            'platforms'    => ['Windows', 'PlayStation'],
            'release_date' => '2025-06-01',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Nuevo Juego Test']);

        $this->assertDatabaseHas('games', ['title' => 'Nuevo Juego Test']);
    }

    public function test_TC028_regular_user_cannot_create_game(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/games', [
            'title' => 'Juego no permitido',
        ]);

        $response->assertStatus(403);
    }

    public function test_TC028_create_game_requires_mandatory_fields(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->postJson('/api/games', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'title', 'description', 'developer',
                     'publisher', 'price', 'genres', 'platforms', 'release_date',
                 ]);
    }

    // ── TC-029: Editar juego ──────────────────────────────────────────────────

    public function test_TC029_admin_can_update_game(): void
    {
        $admin = $this->makeAdmin();
        $game  = $this->makeGame();

        $response = $this->actingAs($admin)->putJson("/api/games/{$game->id}", [
            'price'     => 19.99,
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('games', [
            'id'    => $game->id,
            'price' => 19.99,
        ]);
    }

    public function test_TC029_regular_user_cannot_update_game(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();

        $response = $this->actingAs($user)->putJson("/api/games/{$game->id}", [
            'price' => 0,
        ]);

        $response->assertStatus(403);
    }

    // ── TC-030: Eliminar juego ────────────────────────────────────────────────

    public function test_TC030_admin_can_delete_game(): void
    {
        $admin = $this->makeAdmin();
        $game  = $this->makeGame();

        $response = $this->actingAs($admin)->deleteJson("/api/games/{$game->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('games', ['id' => $game->id]);
    }

    public function test_TC030_regular_user_cannot_delete_game(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();

        $response = $this->actingAs($user)->deleteJson("/api/games/{$game->id}");

        $response->assertStatus(403);
    }

    // ── TC-031: Eliminar usuario ──────────────────────────────────────────────

    public function test_TC031_admin_can_delete_regular_user(): void
    {
        $admin  = $this->makeAdmin();
        $target = User::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/admin/users/{$target->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_TC031_admin_cannot_delete_themselves(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->deleteJson("/api/admin/users/{$admin->id}");

        $response->assertStatus(403)
                 ->assertJson(['message' => 'No puedes eliminar tu propia cuenta.']);
    }

    public function test_TC031_admin_cannot_delete_another_admin(): void
    {
        $admin1 = $this->makeAdmin();
        $admin2 = $this->makeAdmin();

        $response = $this->actingAs($admin1)->deleteJson("/api/admin/users/{$admin2->id}");

        $response->assertStatus(403)
                 ->assertJson(['message' => 'No puedes eliminar a otro administrador.']);
    }

    public function test_TC031_regular_user_cannot_delete_users(): void
    {
        $user   = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/admin/users/{$target->id}");

        $response->assertStatus(403);
    }
}
