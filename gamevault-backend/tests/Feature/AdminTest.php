<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/admin/users');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'total']);
    }

    public function test_regular_user_cannot_list_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_regular_user(): void
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/admin/users/{$target->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->deleteJson("/api/admin/users/{$admin->id}");

        $response->assertStatus(403);
    }

    public function test_admin_cannot_delete_another_admin(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin1)->deleteJson("/api/admin/users/{$admin2->id}");

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_delete_users(): void
    {
        $user   = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/admin/users/{$target->id}");

        $response->assertStatus(403);
    }
}
