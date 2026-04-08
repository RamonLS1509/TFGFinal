<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

// ─── Autenticación (públicas) ────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ─── Juegos (públicos) ───────────────────────────────────────────────────────
Route::get('/games',        [GameController::class, 'index']);
Route::get('/games/{game}', [GameController::class, 'show']);

// ─── Rutas protegidas ────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Biblioteca del usuario
    Route::apiResource('/library', LibraryController::class);

    // Wishlist del usuario
    Route::apiResource('/wishlist', WishlistController::class)->only([
        'index', 'store', 'update', 'destroy',
    ]);

    // Admin: gestión de juegos
    Route::middleware('can:admin')->group(function () {
        Route::get('/admin/games',         [GameController::class, 'adminIndex']);
        Route::post('/games',              [GameController::class, 'store']);
        Route::put('/games/{game}',        [GameController::class, 'update']);
        Route::delete('/games/{game}',     [GameController::class, 'destroy']);
    });
});
