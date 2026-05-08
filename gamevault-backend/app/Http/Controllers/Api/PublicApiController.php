<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Library;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicApiController extends Controller
{
    // ── GET /api/v1/stats ────────────────────────────────────────────────────
    // Estadísticas globales de la plataforma
    public function stats(): JsonResponse
    {
        $stats = Cache::remember('api.stats', 60, function () {
            return [
                'platform' => [
                    'total_games' => Game::active()->count(),
                    'total_users' => User::where('role', 'user')->count(),
                    'total_library_entries' => Library::count(),
                    'total_wishlist_entries' => Wishlist::count(),
                ],
                'generated_at' => now()->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stats,
            'meta' => [
                'api_version' => 'v1',
                'cache_ttl' => '60 seconds',
            ],
        ]);
    }

    // ── GET /api/v1/genres ───────────────────────────────────────────────────
    // Lista todos los géneros disponibles con conteo de juegos
    public function genres(): JsonResponse
    {
        $genres = Cache::remember('api.genres', 300, function () {
            $games = Game::active()->pluck('genres');

            $counts = [];
            foreach ($games as $gameGenres) {
                foreach ($gameGenres as $genre) {
                    $counts[$genre] = ($counts[$genre] ?? 0) + 1;
                }
            }

            arsort($counts);

            return collect($counts)->map(fn($count, $name) => [
                'name' => $name,
                'games_count' => $count,
            ])->values();
        });

        return response()->json([
            'success' => true,
            'data' => $genres,
            'meta' => ['total_genres' => count($genres)],
        ]);
    }

    // ── GET /api/v1/platforms ────────────────────────────────────────────────
    // Lista todas las plataformas con conteo de juegos
    public function platforms(): JsonResponse
    {
        $platforms = Cache::remember('api.platforms', 300, function () {
            $games = Game::active()->pluck('platforms');

            $counts = [];
            foreach ($games as $gamePlatforms) {
                foreach ($gamePlatforms as $platform) {
                    $counts[$platform] = ($counts[$platform] ?? 0) + 1;
                }
            }

            arsort($counts);

            return collect($counts)->map(fn($count, $name) => [
                'name' => $name,
                'games_count' => $count,
            ])->values();
        });

        return response()->json([
            'success' => true,
            'data' => $platforms,
            'meta' => ['total_platforms' => count($platforms)],
        ]);
    }

    // ── GET /api/v1/search ───────────────────────────────────────────────────
    // Búsqueda avanzada de juegos
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['sometimes', 'string', 'min:1', 'max:100'],
            'genre' => ['sometimes', 'string'],
            'platform' => ['sometimes', 'string'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0'],
            'min_score' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Game::active();

        // Solo aplica búsqueda de texto si se proporciona q
        if ($request->has('q') && $request->q !== '*') {
            $q = $request->q;
            $query->where(function ($q2) use ($q) {
                $q2->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('developer', 'like', "%{$q}%")
                    ->orWhere('publisher', 'like', "%{$q}%");
            });
        }

        if ($request->has('genre')) {
            $query->whereJsonContains('genres', $request->genre);
        }

        if ($request->has('platform')) {
            $query->whereJsonContains('platforms', $request->platform);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('min_score')) {
            $query->where('metacritic_score', '>=', $request->min_score);
        }

        $limit = min((int) $request->input('limit', 10), 50);
        $results = $query->limit($limit)
            ->get([
                'id',
                'title',
                'slug',
                'description',
                'developer',
                'price',
                'cover_image',
                'genres',
                'platforms',
                'metacritic_score',
                'release_date'
            ]);

        return response()->json([
            'success' => true,
            'data' => $results->map(fn($g) => [
                'id' => $g->id,
                'title' => $g->title,
                'slug' => $g->slug,
                'description' => substr($g->description, 0, 150) . '...',
                'developer' => $g->developer,
                'price' => $g->price,
                'cover_image' => $g->cover_image,
                'genres' => $g->genres,
                'platforms' => $g->platforms,
                'metacritic_score' => $g->metacritic_score,
                'release_date' => $g->release_date,
            ]),
            'meta' => [
                'query' => $request->q ?? '',
                'results_count' => $results->count(),
                'limit' => $limit,
            ],
        ]);
    }

    // ── GET /api/v1/game/{slug} ──────────────────────────────────────────────
    // Detalle público de un juego por slug
    public function gameBySlug(string $slug): JsonResponse
    {
        $game = Game::active()
            ->where('slug', $slug)
            ->withCount(['owners', 'wishedBy'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $game->id,
                'title' => $game->title,
                'slug' => $game->slug,
                'description' => $game->description,
                'developer' => $game->developer,
                'publisher' => $game->publisher,
                'price' => $game->price,
                'cover_image' => $game->cover_image,
                'genres' => $game->genres,
                'platforms' => $game->platforms,
                'release_date' => $game->release_date,
                'metacritic_score' => $game->metacritic_score,
                'community' => [
                    'owners_count' => $game->owners_count,
                    'wished_count' => $game->wished_by_count,
                ],
            ],
        ]);
    }
}
