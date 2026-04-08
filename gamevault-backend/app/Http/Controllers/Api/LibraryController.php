<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Library;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    // Biblioteca del usuario autenticado
    public function index(Request $request): JsonResponse
    {
        $library = $request->user()
            ->library()
            ->with('game')
            ->orderBy('purchased_at', 'desc')
            ->get();

        return response()->json($library);
    }

    // Añadir juego a la biblioteca (compra)
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'game_id' => ['required', 'exists:games,id'],
        ]);

        $game = Game::findOrFail($request->game_id);

        // Comprobar que no lo tiene ya
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

    // Ver una entrada de la biblioteca
    public function show(Request $request, Library $library): JsonResponse
    {
        $this->authorize('view', $library);

        return response()->json($library->load('game'));
    }

    // Actualizar horas jugadas
    public function update(Request $request, Library $library): JsonResponse
    {
        $this->authorize('update', $library);

        $request->validate([
            'hours_played'   => ['sometimes', 'integer', 'min:0'],
            'last_played_at' => ['sometimes', 'date'],
        ]);

        $library->update($request->only('hours_played', 'last_played_at'));

        return response()->json($library->fresh()->load('game'));
    }

    // Eliminar juego de la biblioteca
    public function destroy(Request $request, Library $library): JsonResponse
    {
        $this->authorize('delete', $library);

        $library->delete();

        return response()->json(['message' => 'Juego eliminado de la biblioteca.']);
    }
}
