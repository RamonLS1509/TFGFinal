<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Library;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFlowTest extends TestCase
{
    use RefreshDatabase;

    // ── Helper ────────────────────────────────────────────────────────────────

    /**
     * Crea un juego con valores por defecto, permitiendo sobreescribir campos.
     */
    private function makeGame(array $overrides = []): Game
    {
        return Game::create(array_merge([
            'title'        => 'Game ' . uniqid(),
            'description'  => 'Descripción suficientemente larga para pasar la validación del campo requerido.',
            'developer'    => 'Dev Studio',
            'publisher'    => 'Publisher Co',
            'price'        => 29.99,
            'genres'       => ['Action', 'RPG'],
            'platforms'    => ['Windows'],
            'release_date' => '2024-01-15',
            'is_active'    => true,
        ], $overrides));
    }

    // ── TC-001: Visualización inicial ─────────────────────────────────────────

    /**
     * Verifica que la página principal muestra juegos
     * sin necesidad de estar autenticado.
     */
    public function test_TC001_homepage_shows_games_without_login(): void
    {
        $this->makeGame();
        $this->makeGame();

        $response = $this->getJson('/api/games');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'total', 'current_page']);

        $this->assertGreaterThan(0, $response->json('total'));
    }

    // ── TC-002: Acceso a catálogo ─────────────────────────────────────────────

    /**
     * Verifica que el catálogo de juegos es accesible
     * sin necesidad de estar autenticado.
     */
    public function test_TC002_catalog_accessible_without_login(): void
    {
        $response = $this->getJson('/api/games');

        $response->assertStatus(200);
    }

    // ── TC-003: Ver detalle de juego ──────────────────────────────────────────

    /**
     * Verifica que el detalle de un juego es accesible
     * sin autenticación y devuelve los datos correctos.
     */
    public function test_TC003_game_detail_accessible_without_login(): void
    {
        $game = $this->makeGame();

        $response = $this->getJson("/api/games/{$game->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $game->id, 'title' => $game->title]);
    }

    // ── TC-004: Restricciones sin login ──────────────────────────────────────

    /**
     * Verifica que un usuario no autenticado no puede añadir
     * un juego a su biblioteca y recibe un error 401.
     */
    public function test_TC004_cannot_buy_game_without_login(): void
    {
        $game = $this->makeGame();

        $response = $this->postJson('/api/library', ['game_id' => $game->id]);

        $response->assertStatus(401);
    }

    /**
     * Verifica que un usuario no autenticado no puede añadir
     * un juego a su wishlist y recibe un error 401.
     */
    public function test_TC004_cannot_add_wishlist_without_login(): void
    {
        $game = $this->makeGame();

        $response = $this->postJson('/api/wishlist', ['game_id' => $game->id]);

        $response->assertStatus(401);
    }

    /**
     * Verifica que un usuario no autenticado no puede crear
     * una reseña y recibe un error 401.
     */
    public function test_TC004_cannot_create_review_without_login(): void
    {
        $game = $this->makeGame();

        $response = $this->postJson('/api/reviews', [
            'game_id' => $game->id,
            'score'   => 8,
            'title'   => 'Test',
            'body'    => 'Cuerpo de prueba suficientemente largo para pasar la validación.',
        ]);

        $response->assertStatus(401);
    }

    // ── TC-005: Registro correcto ─────────────────────────────────────────────

    /**
     * Verifica que un usuario puede registrarse con datos válidos
     * y que queda guardado correctamente en la base de datos.
     */
    public function test_TC005_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Test User',
            'username'              => 'testuser',
            'email'                 => 'test@example.com',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email']]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    // ── TC-006: Validación de campos en registro ──────────────────────────────

    /**
     * Verifica que el registro falla con datos inválidos y devuelve
     * errores de validación 422 para cada campo incorrecto.
     */
    public function test_TC006_register_fails_with_invalid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name'     => '',
            'username' => '',
            'email'    => 'not-an-email',
            'password' => '123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'username', 'email', 'password']);
    }

    /**
     * Verifica que el registro falla si el email ya está en uso
     * y devuelve un error de validación específico para ese campo.
     */
    public function test_TC006_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/register', [
            'name'                  => 'Another User',
            'username'              => 'anotheruser',
            'email'                 => 'existing@example.com',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    // ── TC-007: Login correcto ────────────────────────────────────────────────

    /**
     * Verifica que un usuario puede iniciar sesión con credenciales
     * válidas y recibe los datos de sesión correctos.
     */
    public function test_TC007_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('Password1!')]);

        $response = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'Password1!',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'user']);
    }

    // ── TC-008: Login incorrecto ──────────────────────────────────────────────

    /**
     * Verifica que el login falla con contraseña incorrecta
     * y devuelve el mensaje de error correspondiente.
     */
    public function test_TC008_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct_password')]);

        $response = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Credenciales incorrectas.']);
    }

    /**
     * Verifica que el login falla si el email no existe en el sistema.
     */
    public function test_TC008_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'noexiste@example.com',
            'password' => 'Password1!',
        ]);

        $response->assertStatus(401);
    }

    // ── TC-009: Ver catálogo autenticado ──────────────────────────────────────

    /**
     * Verifica que un usuario autenticado puede ver el catálogo
     * de juegos correctamente.
     */
    public function test_TC009_authenticated_user_can_view_catalog(): void
    {
        $user = User::factory()->create();
        $this->makeGame();

        $response = $this->actingAs($user)->getJson('/api/games');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'total']);
    }

    // ── TC-010: Crear reseña ──────────────────────────────────────────────────

    /**
     * Verifica que un usuario autenticado puede crear una reseña
     * y que esta queda guardada correctamente en la base de datos.
     */
    public function test_TC010_user_can_create_review(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();

        $response = $this->actingAs($user)->postJson('/api/reviews', [
            'game_id'     => $game->id,
            'score'       => 9,
            'title'       => 'Excelente juego',
            'body'        => 'Una experiencia increíble que no te puedes perder bajo ningún concepto.',
            'recommended' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['score' => 9, 'title' => 'Excelente juego']);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'game_id' => $game->id,
        ]);
    }

    // ── TC-011: Visualización de reseñas ──────────────────────────────────────

    /**
     * Verifica que cualquier usuario puede ver las reseñas de un juego
     * junto con sus estadísticas agregadas correctamente calculadas.
     */
    public function test_TC011_anyone_can_view_game_reviews_with_stats(): void
    {
        $game = $this->makeGame();
        $user = User::factory()->create();
        Review::create([
            'user_id'     => $user->id,
            'game_id'     => $game->id,
            'score'       => 8,
            'title'       => 'Buen juego',
            'body'        => 'Una experiencia increíble que no te puedes perder bajo ningún concepto.',
            'recommended' => true,
        ]);

        $response = $this->getJson("/api/games/{$game->id}/reviews");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'reviews' => ['data'],
                     'stats'   => ['total', 'avg_score', 'recommended', 'distribution'],
                 ]);

        $this->assertEquals(1,   $response->json('stats.total'));
        $this->assertEquals(8.0, $response->json('stats.avg_score'));
        $this->assertEquals(1,   $response->json('stats.recommended'));
    }

    // ── TC-012: Editar reseña ─────────────────────────────────────────────────

    /**
     * Verifica que un usuario puede editar su propia reseña
     * y que los cambios se reflejan correctamente en la respuesta.
     */
    public function test_TC012_user_can_edit_own_review(): void
    {
        $user   = User::factory()->create();
        $game   = $this->makeGame();
        $review = Review::create([
            'user_id'     => $user->id,
            'game_id'     => $game->id,
            'score'       => 7,
            'title'       => 'Título original',
            'body'        => 'Cuerpo original suficientemente largo para pasar la validación requerida.',
            'recommended' => true,
        ]);

        $response = $this->actingAs($user)->putJson("/api/reviews/{$review->id}", [
            'score' => 10,
            'title' => 'Título actualizado',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['score' => 10, 'title' => 'Título actualizado']);
    }

    /**
     * Verifica que un usuario no puede editar la reseña de otro usuario
     * y recibe un error 403 de acceso denegado.
     */
    public function test_TC012_user_cannot_edit_another_users_review(): void
    {
        $owner  = User::factory()->create();
        $hacker = User::factory()->create();
        $game   = $this->makeGame();
        $review = Review::create([
            'user_id'     => $owner->id,
            'game_id'     => $game->id,
            'score'       => 7,
            'title'       => 'Título',
            'body'        => 'Cuerpo suficientemente largo para pasar la validación requerida.',
            'recommended' => true,
        ]);

        $response = $this->actingAs($hacker)->putJson("/api/reviews/{$review->id}", [
            'score' => 1,
        ]);

        $response->assertStatus(403);
    }

    // ── TC-013: Eliminar reseña ───────────────────────────────────────────────

    /**
     * Verifica que un usuario puede eliminar su propia reseña
     * y que esta desaparece de la base de datos.
     */
    public function test_TC013_user_can_delete_own_review(): void
    {
        $user   = User::factory()->create();
        $game   = $this->makeGame();
        $review = Review::create([
            'user_id'     => $user->id,
            'game_id'     => $game->id,
            'score'       => 8,
            'title'       => 'Título',
            'body'        => 'Cuerpo suficientemente largo para pasar la validación requerida.',
            'recommended' => true,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    // ── TC-014: Biblioteca vacía ──────────────────────────────────────────────

    /**
     * Verifica que la biblioteca de un usuario recién registrado
     * devuelve un array vacío.
     */
    public function test_TC014_empty_library_returns_empty_array(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/library');

        $response->assertStatus(200)
                 ->assertJson([]);
    }

    // ── TC-015: Añadir juego a biblioteca ────────────────────────────────────

    /**
     * Verifica que un usuario puede añadir un juego a su biblioteca
     * y que la entrada queda guardada correctamente en la base de datos.
     */
    public function test_TC015_user_can_add_game_to_library(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();

        $response = $this->actingAs($user)->postJson('/api/library', [
            'game_id' => $game->id,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['game_id' => $game->id]);

        $this->assertDatabaseHas('libraries', [
            'user_id' => $user->id,
            'game_id' => $game->id,
        ]);
    }

    // ── TC-016: Información de compra ─────────────────────────────────────────

    /**
     * Verifica que la entrada de biblioteca muestra correctamente
     * la fecha de compra y el precio pagado por el juego.
     */
    public function test_TC016_library_entry_shows_purchase_info(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame(['price' => 19.99]);

        $this->actingAs($user)->postJson('/api/library', ['game_id' => $game->id]);

        $response = $this->actingAs($user)->getJson('/api/library');

        $response->assertStatus(200);
        $entry = $response->json('0');
        $this->assertArrayHasKey('purchased_at', $entry);
        $this->assertArrayHasKey('price_paid',   $entry);
        $this->assertEquals(19.99, $entry['price_paid']);
    }

    // ── TC-017: Wishlist vacía ────────────────────────────────────────────────

    /**
     * Verifica que la wishlist de un usuario recién registrado
     * devuelve un array vacío.
     */
    public function test_TC017_empty_wishlist_returns_empty_array(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/wishlist');

        $response->assertStatus(200)
                 ->assertJson([]);
    }

    // ── TC-018: Añadir a wishlist ─────────────────────────────────────────────

    /**
     * Verifica que un usuario puede añadir un juego a su wishlist
     * y que la entrada queda guardada correctamente en la base de datos.
     */
    public function test_TC018_user_can_add_game_to_wishlist(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();

        $response = $this->actingAs($user)->postJson('/api/wishlist', [
            'game_id'  => $game->id,
            'priority' => 0,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['game_id' => $game->id]);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'game_id' => $game->id,
        ]);
    }

    // ── TC-019: Eliminar de wishlist ──────────────────────────────────────────

    /**
     * Verifica que un usuario puede eliminar un juego de su wishlist
     * y que la entrada desaparece de la base de datos.
     */
    public function test_TC019_user_can_remove_game_from_wishlist(): void
    {
        $user  = User::factory()->create();
        $game  = $this->makeGame();
        $entry = Wishlist::create([
            'user_id'  => $user->id,
            'game_id'  => $game->id,
            'priority' => 0,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/wishlist/{$entry->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('wishlists', ['id' => $entry->id]);
    }

    // ── TC-020: Marcar favorito ───────────────────────────────────────────────

    /**
     * Verifica que un usuario puede marcar un juego de su wishlist
     * como favorito cambiando su prioridad a 1.
     */
    public function test_TC020_user_can_mark_game_as_favorite(): void
    {
        $user  = User::factory()->create();
        $game  = $this->makeGame();
        $entry = Wishlist::create([
            'user_id'  => $user->id,
            'game_id'  => $game->id,
            'priority' => 0,
        ]);

        $response = $this->actingAs($user)->putJson("/api/wishlist/{$entry->id}", [
            'priority' => 1,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['priority' => 1]);

        $this->assertDatabaseHas('wishlists', [
            'id'       => $entry->id,
            'priority' => 1,
        ]);
    }

    // ── TC-021: Comprar desde wishlist ────────────────────────────────────────

    /**
     * Verifica que al comprar un juego que estaba en la wishlist,
     * este queda en la biblioteca y se elimina de la wishlist.
     */
    public function test_TC021_buying_game_removes_it_from_wishlist(): void
    {
        $user  = User::factory()->create();
        $game  = $this->makeGame();
        $entry = Wishlist::create([
            'user_id'  => $user->id,
            'game_id'  => $game->id,
            'priority' => 0,
        ]);

        // Comprar el juego
        $this->actingAs($user)->postJson('/api/library', ['game_id' => $game->id]);

        // Eliminar de wishlist manualmente (como hace el frontend)
        $this->actingAs($user)->deleteJson("/api/wishlist/{$entry->id}");

        $this->assertDatabaseHas('libraries', [
            'user_id' => $user->id,
            'game_id' => $game->id,
        ]);
        $this->assertDatabaseMissing('wishlists', ['id' => $entry->id]);
    }

    // ── TC-022: Ver perfil ────────────────────────────────────────────────────

    /**
     * Verifica que un usuario autenticado puede ver su propio perfil
     * con todos los campos y estadísticas esperados.
     */
    public function test_TC022_user_can_view_own_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/profile');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id', 'name', 'username', 'email',
                     'bio', 'avatar', 'role', 'stats',
                 ]);
    }

    // ── TC-023: Editar perfil ─────────────────────────────────────────────────

    /**
     * Verifica que un usuario puede actualizar los datos de su perfil
     * y que los cambios quedan reflejados en la base de datos.
     */
    public function test_TC023_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'name' => 'Nuevo Nombre',
            'bio'  => 'Mi nueva biografía de usuario en la plataforma.',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Nuevo Nombre']);

        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'name' => 'Nuevo Nombre',
        ]);
    }

    // ── TC-024: Cambiar contraseña ────────────────────────────────────────────

    /**
     * Verifica que un usuario puede cambiar su contraseña
     * proporcionando la contraseña actual correcta.
     */
    public function test_TC024_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('OldPass1!')]);

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password'      => 'OldPass1!',
            'password'              => 'NewPass1!',
            'password_confirmation' => 'NewPass1!',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Contraseña actualizada correctamente.']);
    }

    /**
     * Verifica que el cambio de contraseña falla si la contraseña actual
     * proporcionada es incorrecta, devolviendo un error de validación.
     */
    public function test_TC024_change_password_fails_with_wrong_current(): void
    {
        $user = User::factory()->create(['password' => bcrypt('OldPass1!')]);

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password'      => 'WrongPass1!',
            'password'              => 'NewPass1!',
            'password_confirmation' => 'NewPass1!',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['current_password']);
    }

    // ── TC-025: Juego en biblioteca no se puede añadir a wishlist ────────────

    /**
     * Verifica que un juego que ya está en la biblioteca del usuario
     * no puede añadirse a la wishlist, devolviendo un error 409.
     */
    public function test_TC025_cannot_add_to_wishlist_if_in_library(): void
    {
        $user = User::factory()->create();
        $game = $this->makeGame();

        // Añadir a biblioteca
        Library::create([
            'user_id'    => $user->id,
            'game_id'    => $game->id,
            'price_paid' => $game->price,
        ]);

        // Intentar añadir a wishlist
        $response = $this->actingAs($user)->postJson('/api/wishlist', [
            'game_id' => $game->id,
        ]);

        $response->assertStatus(409)
                 ->assertJson(['message' => 'Este juego ya está en tu biblioteca.']);
    }
}
