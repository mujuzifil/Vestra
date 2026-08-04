<?php

namespace Tests\Feature\Admin;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Filament\Pages\Workspace\TasksPage;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class TasksPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_tasks_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.tasks'));
        $this->assertTrue(Route::has('filament.admin.workspace.tasks.export'));
        $this->assertStringContainsString('/tasks', TasksPage::getUrl());
    }

    public function test_admin_can_access_tasks_page(): void
    {
        Livewire::actingAs($this->admin())
            ->test(TasksPage::class)
            ->assertSuccessful()
            ->assertSee('Tasks')
            ->assertSee('Manage work, assignments, and priorities')
            ->assertDontSee('Import');
    }

    public function test_non_admin_is_denied_access_to_tasks_page(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(TasksPage::class)
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_tasks_route(): void
    {
        $this->get('/tasks')->assertRedirect();
    }

    public function test_empty_state_copy_and_create_action(): void
    {
        Livewire::actingAs($this->admin())
            ->test(TasksPage::class)
            ->assertSee('No tasks found')
            ->assertSee('Tasks will appear here once they are created')
            ->assertSee('Create your first task to begin managing work')
            ->assertSee('Create Task');
    }

    public function test_export_url_includes_filters(): void
    {
        $url = Livewire::actingAs($this->admin())
            ->test(TasksPage::class)
            ->set('search', 'follow-up')
            ->set('statusFilter', [TaskStatus::NEW->value])
            ->instance()
            ->getExportUrl('csv');

        $this->assertStringContainsString('format=csv', $url);
        $this->assertStringContainsString('search=follow-up', $url);
        $this->assertStringContainsString('status', $url);
    }

    public function test_export_route_requires_admin(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('filament.admin.workspace.tasks.export', ['format' => 'csv']))
            ->assertForbidden();
    }

    public function test_export_route_downloads_csv_for_admin(): void
    {
        $admin = $this->admin();

        Task::factory()->create([
            'title' => 'Exportable Task',
            'status' => TaskStatus::NEW,
            'priority' => TaskPriority::MEDIUM,
            'created_by_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('filament.admin.workspace.tasks.export', ['format' => 'csv']));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_export_rows_respect_filters(): void
    {
        $admin = $this->admin();

        Task::factory()->create([
            'title' => 'Keep Me',
            'status' => TaskStatus::NEW,
            'priority' => TaskPriority::MEDIUM,
            'created_by_id' => $admin->id,
        ]);

        Task::factory()->create([
            'title' => 'Skip Me',
            'status' => TaskStatus::COMPLETED,
            'priority' => TaskPriority::LOW,
            'created_by_id' => $admin->id,
        ]);

        $rows = app(\App\Services\Admin\TaskService::class)->exportRows([
            'status' => [TaskStatus::NEW->value],
        ]);

        $titles = array_column($rows, 'title');
        $this->assertContains('Keep Me', $titles);
        $this->assertNotContains('Skip Me', $titles);
    }
}
