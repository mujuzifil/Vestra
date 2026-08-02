<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TasksPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_access_tasks_page(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/tasks');

        $response->assertOk();
        $response->assertSee('Tasks');
        $response->assertSee('Manage work, assignments, and priorities');
    }

    public function test_non_admin_is_redirected_from_tasks_page(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin/tasks');

        $response->assertRedirect();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/tasks');

        $response->assertRedirect('/admin/login');
    }
}
