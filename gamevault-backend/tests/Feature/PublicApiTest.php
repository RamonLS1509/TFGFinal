<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Library;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeGame(array $overrides = []): Game
    {
        return Game::create(array_merge([
            'title' => 'Game ' . uniqid(),
            'description' => 'Descripción larga suficiente para los tests de la api pública.',
            'developer' => 'Dev Studio',
            'publisher' => 'Publisher Co',
            'price' => 29.99,
            'genres' => ['Action', 'RPG'],
            'platforms' => ['Windows', 'PlayStation'],
            'release_date' => '2024-01-15',
            'metacritic_score' => 85,
            'is_active' => true,
        ], $overrides));
    }

    public function test_stats_endpoint_returns_correct_structure(): void
    {
        $this->makeGame();
        $response = $this->getJson('/api/v1/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'platform' => ['total_games', 'total_users', 'total_library_entries', 'total_wishlist_entries'],
                    'games' => ['avg_price', 'max_price', 'min_price', 'free_games', 'avg_metacritic'],
                    'generated_at',
                ],
                'meta',
            ]);

        $this->assertTrue($response->json('success'));
    }

    public function test_stats_counts_only_active_games(): void
    {
        $this->makeGame(['is_active' => true]);
        $this->makeGame(['is_active' => true]);
        $this->makeGame(['is_active' => false]);

        $response = $this->getJson('/api/v1/stats');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.platform.total_games'));
    }

    public function test_rankings_returns_most_owned_by_default(): void
    {
        $this->makeGame();
        $response = $this->getJson('/api/v1/rankings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => ['type', 'limit', 'available_types'],
            ]);

        $this->assertEquals('most_owned', $response->json('meta.type'));
    }

    public function test_rankings_top_rated_returns_games_ordered_by_score(): void
    {
        $this->makeGame(['title' => 'Low Score', 'metacritic_score' => 60]);
        $this->makeGame(['title' => 'High Score', 'metacritic_score' => 95]);
        $this->makeGame(['title' => 'Mid Score', 'metacritic_score' => 75]);

        $response = $this->getJson('/api/v1/rankings?type=top_rated');

        $response->assertStatus(200);
        $this->assertEquals('High Score', $response->json('data.0.title'));
    }

    public function test_rankings_respects_limit_param(): void
    {
        for ($i = 0; $i < 5; $i++)
            $this->makeGame();

        $response = $this->getJson('/api/v1/rankings?limit=3');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_genres_endpoint_returns_all_genres(): void
    {
        $this->makeGame(['genres' => ['Action', 'RPG']]);
        $this->makeGame(['genres' => ['Action', 'Indie']]);

        $response = $this->getJson('/api/v1/genres');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [['name', 'games_count']],
                'meta' => ['total_genres'],
            ]);

        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertContains('Action', $names);
        $this->assertContains('RPG', $names);
        $this->assertContains('Indie', $names);
    }

    public function test_genres_counts_are_correct(): void
    {
        $this->makeGame(['genres' => ['Action', 'RPG']]);
        $this->makeGame(['genres' => ['Action']]);

        $response = $this->getJson('/api/v1/genres');
        $data = collect($response->json('data'));
        $actionRow = $data->firstWhere('name', 'Action');

        $this->assertEquals(2, $actionRow['games_count']);
    }

    public function test_platforms_endpoint_returns_all_platforms(): void
    {
        $this->makeGame(['platforms' => ['Windows', 'PlayStation']]);

        $response = $this->getJson('/api/v1/platforms');

        $response->assertStatus(200);
        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertContains('Windows', $names);
        $this->assertContains('PlayStation', $names);
    }

    public function test_search_requires_query_param(): void
    {
        $response = $this->getJson('/api/v1/search');
        $response->assertStatus(422);
    }

    public function test_search_requires_minimum_2_chars(): void
    {
        $response = $this->getJson('/api/v1/search?q=a');
        $response->assertStatus(422);
    }

    public function test_search_finds_game_by_title(): void
    {
        $this->makeGame(['title' => 'Elden Ring']);
        $this->makeGame(['title' => 'Cyberpunk 2077']);

        $response = $this->getJson('/api/v1/search?q=Elden');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.results_count'));
        $this->assertEquals('Elden Ring', $response->json('data.0.title'));
    }

    public function test_search_finds_game_by_developer(): void
    {
        $this->makeGame(['title' => 'Game A', 'developer' => 'FromSoftware']);
        $this->makeGame(['title' => 'Game B', 'developer' => 'CD Projekt']);

        $response = $this->getJson('/api/v1/search?q=FromSoftware');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.results_count'));
    }

    public function test_search_filters_by_max_price(): void
    {
        $this->makeGame(['title' => 'Cheap Game', 'price' => 5.00]);
        $this->makeGame(['title' => 'Expensive Game', 'price' => 60.00]);

        $response = $this->getJson('/api/v1/search?q=Game&max_price=10');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.results_count'));
        $this->assertEquals('Cheap Game', $response->json('data.0.title'));
    }

    public function test_game_by_slug_returns_correct_game(): void
    {
        $game = $this->makeGame(['title' => 'Elden Ring']);

        $response = $this->getJson("/api/v1/game/{$game->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'description',
                    'developer',
                    'publisher',
                    'price',
                    'genres',
                    'platforms',
                    'metacritic_score',
                    'community' => ['owners_count', 'wished_count'],
                ],
            ]);

        $this->assertEquals($game->slug, $response->json('data.slug'));
    }

    public function test_game_by_slug_returns_404_for_unknown_slug(): void
    {
        $response = $this->getJson('/api/v1/game/juego-que-no-existe');
        $response->assertStatus(404);
    }

    public function test_game_by_slug_includes_community_stats(): void
    {
        $game = $this->makeGame();
        $user = User::factory()->create();
        Library::create(['user_id' => $user->id, 'game_id' => $game->id, 'price_paid' => 0]);
        Wishlist::create(['user_id' => $user->id, 'game_id' => $game->id]);

        // Necesitamos otro usuario para wishlist (no puede estar en biblioteca y wishlist a la vez normalmente)
        $user2 = User::factory()->create();
        Wishlist::create(['user_id' => $user2->id, 'game_id' => $game->id]);

        $response = $this->getJson("/api/v1/game/{$game->slug}");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.community.owners_count'));
        $this->assertEquals(2, $response->json('data.community.wished_count'));
    }
}
