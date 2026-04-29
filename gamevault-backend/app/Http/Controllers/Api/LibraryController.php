<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Library;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $library = $request->user()
            ->library()
            ->with('game')
            ->orderBy('purchased_at', 'desc')
            ->get();

        return response()->json($library);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['game_id' => ['required', 'exists:games,id']]);

        $game = Game::findOrFail($request->game_id);

        $exists = Library::where('user_id', $request->user()->id)
                          ->where('game_id', $game->id)
                          ->exists();

        if ($exists) {
            return response()->json(['message' => 'Ya tienes este juego en tu biblioteca.'], 409);
        }

        $entry = Library::create([
            'user_id'      => $request->user()->id,
            'game_id'      => $game->id,
            'price_paid'   => $game->price,
            'purchased_at' => now(),
        ]);

        return response()->json($entry->load('game'), 201);
    }

    public function show(Request $request, Library $library): JsonResponse
    {
        if ($request->user()->id !== $library->user_id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return response()->json($library->load('game'));
    }

    public function destroy(Request $request, Library $library): JsonResponse
    {
        if ($request->user()->id !== $library->user_id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $library->delete();

        return response()->json(['message' => 'Juego eliminado de la biblioteca.']);
    }
}
