<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Library;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//Gestiona la biblioteca personal de juegos de cada usuario
class LibraryController extends Controller
{
    //Devuelve todos los juegos de la biblioteca del usuario autenticado
    public function index(Request $request): JsonResponse
    {
        $library = $request->user()
            ->library()
            ->with('game')
            ->orderBy('purchased_at', 'desc')
            ->get();

        return response()->json($library);
    }

    //Añade un juego a la biblioteca del usuario (simula una compra)
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
            'user_id' => $request->user()->id,
            'game_id' => $game->id,
            'price_paid' => $game->price,
            'purchased_at' => now(),
        ]);

        return response()->json($entry->load('game'), 201);
    }

    //Devuelve el detalle de una entrada concreta de la biblioteca
    public function show(Request $request, Library $library): JsonResponse
    {
        if ($request->user()->id !== $library->user_id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return response()->json($library->load('game'));
    }

    //Elimina un juego de la biblioteca del usuario
    public function destroy(Request $request, Library $library): JsonResponse
    {
        if ($request->user()->id !== $library->user_id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $library->delete();

        return response()->json(['message' => 'Juego eliminado de la biblioteca.']);
    }
}
