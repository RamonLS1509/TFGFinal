<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\PublicApiController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;

// ─── API Pública v1 ───────────────────────────────────────────────────────────
Route::prefix('v1')
    ->withoutMiddleware(\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class)
    ->group(function () {
        Route::get('/stats',       [PublicApiController::class, 'stats']);
        Route::get('/rankings',    [PublicApiController::class, 'rankings']);
        Route::get('/genres',      [PublicApiController::class, 'genres']);
        Route::get('/platforms',   [PublicApiController::class, 'platforms']);
        Route::get('/search',      [PublicApiController::class, 'search']);
        Route::get('/game/{slug}', [PublicApiController::class, 'gameBySlug']);
    });

// ─── Auth públicas ────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ─── Juegos públicos ──────────────────────────────────────────────────────────
Route::get('/games',             [GameController::class, 'index']);
Route::get('/games/{game}',      [GameController::class, 'show']);

// ─── Reseñas públicas ─────────────────────────────────────────────────────────
Route::get('/games/{game}/reviews', [ReviewController::class, 'index']);

// ─── Rutas protegidas ─────────────────────────────────────────────────────────
Route::middleware([
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'auth:sanctum',
])->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Perfil
    Route::get('/profile',           [ProfileController::class, 'show']);
    Route::put('/profile',           [ProfileController::class, 'update']);
    Route::put('/profile/password',  [ProfileController::class, 'changePassword']);

    // Biblioteca
    Route::apiResource('/library', LibraryController::class);

    // Wishlist
    Route::apiResource('/wishlist', WishlistController::class)->only([
        'index', 'store', 'update', 'destroy',
    ]);

    // Reseñas — rutas estáticas ANTES que las dinámicas
    Route::get('/reviews/my',            [ReviewController::class, 'myReviews']);
    Route::post('/reviews',              [ReviewController::class, 'store']);
    Route::put('/reviews/{review}',      [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}',   [ReviewController::class, 'destroy']);

    // Reseña del usuario para un juego concreto
    Route::get('/games/{game}/reviews/mine', [ReviewController::class, 'userReview']);

    // Admin
    Route::middleware('admin')->group(function () {
    Route::get('/admin/games',          [GameController::class,  'adminIndex']);
    Route::post('/games',               [GameController::class,  'store']);
    Route::put('/games/{game}',         [GameController::class,  'update']);
    Route::delete('/games/{game}',      [GameController::class,  'destroy']);

    // Gestión de usuarios
    Route::get('/admin/users',          [AdminController::class, 'users']);
    Route::delete('/admin/users/{user}',[AdminController::class, 'deleteUser']);
});
});
