<?php

namespace Tests\Feature\Policy;

use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_perform_all_task_actions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $task = Task::factory()->create(['created_by_id' => $admin->id]);

        $this->assertTrue($admin->can('viewAny', Task::class));
        $this->assertTrue($admin->can('view', $task));
        $this->assertTrue($admin->can('create', Task::class));
        $this->assertTrue($admin->can('update', $task));
        $this->assertTrue($admin->can('delete', $task));
        $this->assertTrue($admin->can('assign', $task));
        $this->assertTrue($admin->can('complete', $task));
        $this->assertTrue($admin->can('archive', $task));
    }

    public function test_non_admin_cannot_perform_task_actions(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $task = Task::factory()->create(['created_by_id' => $user->id]);

        $this->assertFalse($user->can('viewAny', Task::class));
        $this->assertFalse($user->can('view', $task));
        $this->assertFalse($user->can('create', Task::class));
        $this->assertFalse($user->can('update', $task));
        $this->assertFalse($user->can('delete', $task));
        $this->assertFalse($user->can('assign', $task));
        $this->assertFalse($user->can('complete', $task));
        $this->assertFalse($user->can('archive', $task));
    }

    public function test_super_administrator_role_can_access_tasks(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $user->assignRole('Super Administrator');
        $task = Task::factory()->create(['created_by_id' => $user->id]);

        $this->assertTrue($user->can('viewAny', Task::class));
        $this->assertTrue($user->can('update', $task));
    }
}
