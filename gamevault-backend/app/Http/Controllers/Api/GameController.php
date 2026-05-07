<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Game\StoreGameRequest;
use App\Http\Requests\Game\UpdateGameRequest;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    // Público: listado con filtros y paginación
    public function index(Request $request): JsonResponse
    {
        $query = Game::active();

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('genre')) {
            $query->byGenre($request->genre);
        }

        if ($request->has('platform')) {
            $query->whereJsonContains('platforms', $request->platform);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $sortField = in_array($request->sort, ['price', 'release_date', 'title'])
            ? $request->sort : 'created_at';
        $sortDir = $request->direction === 'asc' ? 'asc' : 'desc';

        $games = $query->orderBy($sortField, $sortDir)->paginate(12);

        return response()->json($games);
    }

    // Público: detalle de un juego
    public function show(Game $game): JsonResponse
    {
        return response()->json($game);
    }

    // Admin: crear juego
    public function store(StoreGameRequest $request): JsonResponse
    {
        $game = Game::create($request->validated());

        return response()->json($game, 201);
    }

    // Admin: actualizar juego
    public function update(UpdateGameRequest $request, Game $game): JsonResponse
    {
        $game->update($request->validated());

        return response()->json($game);
    }

    // Admin: eliminar juego
    public function destroy(Game $game): JsonResponse
    {
        $game->delete();

        return response()->json(['message' => 'Juego eliminado.'], 204);
    }

    // Admin: listado completo (incluye inactivos)
    public function adminIndex(): JsonResponse
    {
        $games = Game::orderBy('created_at', 'desc')->paginate(20);
        return response()->json($games);
    }
}
