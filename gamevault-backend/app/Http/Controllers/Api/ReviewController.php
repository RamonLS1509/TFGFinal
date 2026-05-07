<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Models\Game;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // Público: reseñas de un juego
    public function index(Request $request, Game $game): JsonResponse
    {
        $page = (int) $request->input('page', 1);

        $reviews = $game->reviews()
            ->with('user:id,name,username,avatar')
            ->latest()
            ->paginate(10, ['*'], 'page', $page);

        $total = $game->reviews()->count();
        $recommended = $game->reviews()->where('recommended', true)->count();

        $stats = [
            'total' => $total,
            'avg_score' => $total > 0 ? round($game->reviews()->avg('score'), 1) : 0,
            'recommended' => $recommended,
            'distribution' => $game->reviews()
                ->selectRaw('score, count(*) as count')
                ->groupBy('score')
                ->orderBy('score', 'desc')
                ->pluck('count', 'score')
                ->toArray(),
        ];

        return response()->json([
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }

    // Privado: crear reseña
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $exists = Review::where('user_id', $request->user()->id)
            ->where('game_id', $request->game_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Ya has escrito una reseña para este juego.',
            ], 409);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'game_id' => $request->game_id,
            'score' => $request->score,
            'title' => $request->title,
            'body' => $request->body,
            'recommended' => $request->recommended ?? true,
        ]);

        return response()->json($review->load('user:id,name,username,avatar'), 201);
    }

    // Privado: actualizar reseña propia
    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        $review->update($request->validated());

        return response()->json($review->fresh()->load('user:id,name,username,avatar'));
    }

    // Privado: eliminar reseña propia
    public function destroy(Request $request, Review $review): JsonResponse
    {
        if ($request->user()->id !== $review->user_id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Reseña eliminada.']);
    }


    // Público: reseña de un usuario concreto para un juego
    public function userReview(Request $request, Game $game): JsonResponse
{
    if (!$request->user()) {
        return response()->json(null);
    }

    $review = Review::where('user_id', $request->user()->id)
                    ->where('game_id', $game->id)
                    ->first();

    // first() devuelve null si no existe, nunca lanza 404
    return response()->json($review);
}
}
