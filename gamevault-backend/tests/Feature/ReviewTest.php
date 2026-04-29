<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function makeGame(array $overrides = []): Game
    {
        return Game::create(array_merge([
            'title'        => 'Game ' . uniqid(),
            'description'  => 'Descripción suficientemente larga para pasar la validación.',
            'developer'    => 'Dev',
            'publisher'    => 'Pub',
            'price'        => 19.99,
            'genres'       => ['Action'],
            'platforms'    => ['Windows'],
            'release_date' => '2024-01-01',
            'is_active'    => true,
        ], $overrides));
    }

    private function makeReview(User $user, Game $game, array $overrides = []): Review
    {
        return Review::create(array_merge([
            'user_id'     => $user->id,
            'game_id'     => $game->id,
            'score'       => 8,
            'title'       => 'Gran juego',
            'body'        => 'Este juego es una obra maestra del género que vale la pena jugar.',
            'recommended' => true,
        ], $overrides));
    }

    public function test_anyone_can_list_reviews_of_a_game(): void
    {
        $game = $this->makeGame();
        $user = User::factory()->create();
        $this->makeReview($user, $game);

        $response = $this->getJson("/api/games/{$game->id}/reviews");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'reviews' => ['data'],
                     'stats'   => ['total', 'avg_score', 'recommended', 'distribution'],
                 ]);
    }

    public function test_authenticated_user_can_create_review(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();

        $response = $this->actingAs($user)->postJson('/api/reviews', [
            'game_id'     => $game->id,
            'score'       => 9,
            'title'       => 'Excelente juego',
            'body'        => 'Una experiencia increíble que no te puedes perder bajo ningún concepto.',
            'recommended' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['score' => 9, 'title' => 'Excelente juego']);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'score'   => 9,
        ]);
    }

    public function test_user_cannot_review_same_game_twice(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();
        $this->makeReview($user, $game);

        $response = $this->actingAs($user)->postJson('/api/reviews', [
            'game_id'     => $game->id,
            'score'       => 5,
            'title'       => 'Segunda reseña',
            'body'        => 'Intento escribir una segunda reseña para el mismo juego.',
            'recommended' => false,
        ]);

        $response->assertStatus(409);
    }

    public function test_review_requires_minimum_body_length(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();

        $response = $this->actingAs($user)->postJson('/api/reviews', [
            'game_id' => $game->id,
            'score'   => 8,
            'title'   => 'Corta',
            'body'    => 'Muy corto',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['body']);
    }

    public function test_score_must_be_between_1_and_10(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();

        $response = $this->actingAs($user)->postJson('/api/reviews', [
            'game_id' => $game->id,
            'score'   => 11,
            'title'   => 'Título válido',
            'body'    => 'Cuerpo suficientemente largo para pasar la validación mínima requerida.',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['score']);
    }

    public function test_user_can_update_own_review(): void
    {
        $user   = User::factory()->create();
        $game   = $this->makeGame();
        $review = $this->makeReview($user, $game);

        $response = $this->actingAs($user)->putJson("/api/reviews/{$review->id}", [
            'score' => 10,
            'title' => 'Título actualizado',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['score' => 10]);
    }

    public function test_user_cannot_update_another_users_review(): void
    {
        $owner  = User::factory()->create();
        $hacker = User::factory()->create();
        $game   = $this->makeGame();
        $review = $this->makeReview($owner, $game);

        $response = $this->actingAs($hacker)->putJson("/api/reviews/{$review->id}", [
            'score' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_review(): void
    {
        $user   = User::factory()->create();
        $game   = $this->makeGame();
        $review = $this->makeReview($user, $game);

        $response = $this->actingAs($user)->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_admin_can_delete_any_review(): void
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $user   = User::factory()->create();
        $game   = $this->makeGame();
        $review = $this->makeReview($user, $game);

        $response = $this->actingAs($admin)->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_create_review(): void
    {
        $game = $this->makeGame();

        $response = $this->postJson('/api/reviews', [
            'game_id' => $game->id,
            'score'   => 8,
            'title'   => 'Sin auth',
            'body'    => 'Intento crear una reseña sin estar autenticado en la aplicación.',
        ]);

        $response->assertStatus(401);
    }

    public function test_stats_calculate_correctly(): void
    {
        $game  = $this->makeGame();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->makeReview($user1, $game, ['score' => 8, 'recommended' => true]);
        $this->makeReview($user2, $game, ['score' => 6, 'recommended' => false]);

        $response = $this->getJson("/api/games/{$game->id}/reviews");

        $response->assertStatus(200);
        $this->assertEquals(2,   $response->json('stats.total'));
        $this->assertEquals(7.0, $response->json('stats.avg_score'));
        $this->assertEquals(1,   $response->json('stats.recommended'));
    }
}
