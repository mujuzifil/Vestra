<?php

namespace Tests\Feature\Admin;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Services\Admin\TaskService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_it_creates_a_task_with_default_status_and_priority(): void
    {
        $creator = User::factory()->create(['is_admin' => true]);
        $this->actingAs($creator);

        $service = app(TaskService::class);

        $task = $service->createTask($creator, [
            'title' => 'Follow up with distributor',
            'description' => 'Call back to confirm interest',
        ]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Follow up with distributor', $task->title);
        $this->assertEquals(TaskStatus::NEW, $task->status);
        $this->assertEquals(TaskPriority::MEDIUM, $task->priority);
        $this->assertEquals($creator->id, $task->created_by_id);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'task.created',
            'subject_id' => $task->id,
            'subject_type' => $task->getMorphClass(),
        ]);
    }

    public function test_creating_a_task_with_assignee_sets_status_to_assigned(): void
    {
        $creator = User::factory()->create(['is_admin' => true]);
        $assignee = User::factory()->create(['is_admin' => true]);
        $this->actingAs($creator);

        $service = app(TaskService::class);

        $task = $service->createTask($creator, [
            'title' => 'Review quote',
            'assignee_id' => $assignee->id,
        ]);

        $this->assertEquals(TaskStatus::ASSIGNED, $task->status);
        $this->assertEquals($assignee->id, $task->assignee_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'task.assigned']);
    }

    public function test_it_updates_a_task_and_logs_activity(): void
    {
        $creator = User::factory()->create(['is_admin' => true]);
        $this->actingAs($creator);

        $service = app(TaskService::class);
        $task = Task::factory()->create([
            'created_by_id' => $creator->id,
            'status' => TaskStatus::NEW,
        ]);

        $updated = $service->updateTask($task, [
            'title' => 'Updated title',
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);

        $this->assertEquals('Updated title', $updated->title);
        $this->assertEquals(TaskStatus::IN_PROGRESS, $updated->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'task.updated',
            'subject_id' => $task->id,
        ]);
    }

    public function test_completing_a_task_sets_completed_at(): void
    {
        $creator = User::factory()->create(['is_admin' => true]);
        $this->actingAs($creator);

        $task = Task::factory()->create([
            'created_by_id' => $creator->id,
            'status' => TaskStatus::IN_PROGRESS,
        ]);

        app(TaskService::class)->completeTask($task);

        $task->refresh();
        $this->assertEquals(TaskStatus::COMPLETED, $task->status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_it_soft_deletes_a_task(): void
    {
        $creator = User::factory()->create(['is_admin' => true]);
        $this->actingAs($creator);

        $task = Task::factory()->create(['created_by_id' => $creator->id]);

        app(TaskService::class)->deleteTask($task);

        $this->assertSoftDeleted($task);
    }

    public function test_overdue_scope_returns_only_open_past_due_tasks(): void
    {
        $creator = User::factory()->create(['is_admin' => true]);

        $overdue = Task::factory()->create([
            'created_by_id' => $creator->id,
            'due_date' => now()->subDay(),
            'status' => TaskStatus::IN_PROGRESS,
            'completed_at' => null,
        ]);

        Task::factory()->create([
            'created_by_id' => $creator->id,
            'due_date' => now()->subDay(),
            'status' => TaskStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        Task::factory()->create([
            'created_by_id' => $creator->id,
            'due_date' => now()->addDay(),
            'status' => TaskStatus::IN_PROGRESS,
            'completed_at' => null,
        ]);

        $results = Task::query()->overdue()->pluck('id');

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($overdue->id));
    }

    public function test_search_scope_filters_by_title_and_assignee(): void
    {
        $creator = User::factory()->create(['is_admin' => true]);
        $assignee = User::factory()->create(['is_admin' => true, 'name' => 'Alice Tester']);

        $task = Task::factory()->create([
            'created_by_id' => $creator->id,
            'assignee_id' => $assignee->id,
            'title' => 'Quarterly review',
        ]);

        Task::factory()->create([
            'created_by_id' => $creator->id,
            'title' => 'Unrelated task',
        ]);

        $this->assertTrue(Task::query()->search('Quarterly')->get()->contains($task));
        $this->assertTrue(Task::query()->search('Alice')->get()->contains($task));
    }

    public function test_kpi_cards_return_real_counts(): void
    {
        $creator = User::factory()->create(['is_admin' => true]);

        Task::factory()->count(3)->create([
            'created_by_id' => $creator->id,
            'status' => TaskStatus::COMPLETED,
        ]);

        Task::factory()->count(2)->create([
            'created_by_id' => $creator->id,
            'status' => TaskStatus::IN_PROGRESS,
        ]);

        Task::factory()->count(1)->create([
            'created_by_id' => $creator->id,
            'status' => TaskStatus::IN_PROGRESS,
            'due_date' => now()->subDay(),
            'completed_at' => null,
        ]);

        $kpis = app(TaskService::class)->getKpiCards();

        $this->assertCount(4, $kpis);
        $this->assertEquals('6', str_replace(',', '', $kpis[0]['value']));
        $this->assertEquals('3', str_replace(',', '', $kpis[1]['value']));
        $this->assertEquals('2', str_replace(',', '', $kpis[2]['value']));
        $this->assertEquals('1', str_replace(',', '', $kpis[3]['value']));
    }

    public function test_pagination_respects_status_filter(): void
    {
        $creator = User::factory()->create(['is_admin' => true]);

        Task::factory()->count(5)->create([
            'created_by_id' => $creator->id,
            'status' => TaskStatus::NEW,
        ]);

        Task::factory()->count(3)->create([
            'created_by_id' => $creator->id,
            'status' => TaskStatus::COMPLETED,
        ]);

        $paginator = app(TaskService::class)->paginateTasks([
            'status' => [TaskStatus::NEW->value],
        ]);

        $this->assertEquals(5, $paginator->total());
    }
}
