<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Library;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $wishlist = $request->user()
            ->wishlist()
            ->with('game')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($wishlist);
    }

    public function store(Request $request): JsonResponse
{
    $request->validate([
        'game_id'  => ['required', 'exists:games,id'],
        'priority' => ['sometimes', 'integer', 'min:0', 'max:1'],
    ]);

    // No se puede añadir a wishlist si ya está en biblioteca
    $inLibrary = Library::where('user_id', $request->user()->id)
                                     ->where('game_id', $request->game_id)
                                     ->exists();

    if ($inLibrary) {
        return response()->json([
            'message' => 'Este juego ya está en tu biblioteca.',
        ], 409);
    }

    $exists = Wishlist::where('user_id', $request->user()->id)
                       ->where('game_id', $request->game_id)
                       ->exists();

    if ($exists) {
        return response()->json(['message' => 'El juego ya está en tu lista de deseos.'], 409);
    }

    $entry = Wishlist::create([
        'user_id'  => $request->user()->id,
        'game_id'  => $request->game_id,
        'priority' => $request->priority ?? 0,
    ]);

    return response()->json($entry->load('game'), 201);
}

    public function update(Request $request, Wishlist $wishlist): JsonResponse
    {
        if ($request->user()->id !== $wishlist->user_id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $request->validate([
            'priority' => ['required', 'integer', 'min:0', 'max:1'],
        ]);

        $wishlist->update(['priority' => $request->priority]);

        return response()->json($wishlist->fresh()->load('game'));
    }

    public function destroy(Request $request, Wishlist $wishlist): JsonResponse
    {
        if ($request->user()->id !== $wishlist->user_id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $wishlist->delete();

        return response()->json(['message' => 'Juego eliminado de la wishlist.']);
    }
}
