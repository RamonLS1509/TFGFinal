<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\PublicApiController;
use Illuminate\Support\Facades\Route;

// ─── API Pública v1 — sin ningún middleware ──────────────────────────────────
Route::prefix('v1')->withoutMiddleware([
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    \Illuminate\Auth\Middleware\Authenticate::class,
])->group(function () {
    Route::get('/stats',       [PublicApiController::class, 'stats']);
    Route::get('/rankings',    [PublicApiController::class, 'rankings']);
    Route::get('/genres',      [PublicApiController::class, 'genres']);
    Route::get('/platforms',   [PublicApiController::class, 'platforms']);
    Route::get('/search',      [PublicApiController::class, 'search']);
    Route::get('/game/{slug}', [PublicApiController::class, 'gameBySlug']);
});

// ─── Auth (públicas) ─────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ─── Juegos públicos ─────────────────────────────────────────────────────────
Route::get('/games',        [GameController::class, 'index']);
Route::get('/games/{game}', [GameController::class, 'show']);

// ─── Rutas protegidas con Sanctum ────────────────────────────────────────────
Route::middleware([
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'auth:sanctum',
])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    Route::apiResource('/library', LibraryController::class);

    Route::apiResource('/wishlist', WishlistController::class)->only([
        'index', 'store', 'update', 'destroy',
    ]);

    Route::middleware('admin')->group(function () {
        Route::get('/admin/games',     [GameController::class, 'adminIndex']);
        Route::post('/games',          [GameController::class, 'store']);
        Route::put('/games/{game}',    [GameController::class, 'update']);
        Route::delete('/games/{game}', [GameController::class, 'destroy']);
    });
});
