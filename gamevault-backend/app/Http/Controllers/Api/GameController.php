<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Game\StoreGameRequest;
use App\Http\Requests\Game\UpdateGameRequest;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//Gestiona todo lo relacionado con los juegos.
class GameController extends Controller
{
    // Devuelve el listado paginado de juegos activos con filtros opcionales
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

    // Devuelve el detalle completo de un juego por su ID
    public function show(Game $game): JsonResponse
    {
        return response()->json($game);
    }

    // Crea un nuevo juego en la BBDD
    public function store(StoreGameRequest $request): JsonResponse
    {
        $game = Game::create($request->validated());

        return response()->json($game, 201);
    }

    // Actualiza los datos de un juego existente
    public function update(UpdateGameRequest $request, Game $game): JsonResponse
    {
        $game->update($request->validated());

        return response()->json($game);
    }

    // Admin: Elimina un juego de la BBDD
    public function destroy(Game $game): JsonResponse
    {
        $game->delete();

        return response()->json(['message' => 'Juego eliminado.'], 204);
    }

    // Devuelve el listado completo de juegos para el panel de control de juegos
    public function adminIndex(): JsonResponse
    {
        $games = Game::orderBy('created_at', 'desc')->paginate(20);
        return response()->json($games);
    }
}
