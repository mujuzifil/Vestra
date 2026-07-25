<?php

namespace Tests\Unit\Services;

use App\Enums\WorkflowStatus;
use App\Models\AutomatedWorkflow;
use App\Models\User;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_runs_when_conditions_match(): void
    {
        $user = User::factory()->create();

        $workflow = AutomatedWorkflow::create([
            'name' => 'High value order notification',
            'event' => 'order.created',
            'conditions' => [
                ['key' => 'order.total_amount', 'operator' => 'greater_than', 'value' => '100000'],
            ],
            'action' => 'notification',
            'action_config' => [
                ['key' => 'user_id', 'value' => (string) $user->id],
                ['key' => 'title', 'value' => 'High value order'],
            ],
            'status' => WorkflowStatus::ACTIVE,
        ]);

        app(WorkflowService::class)->trigger('order.created', [
            'order' => ['total_amount' => 250000],
        ]);

        $workflow->refresh();
        $this->assertSame(1, $workflow->run_count);
        $this->assertNotNull($workflow->last_run_at);
    }

    public function test_workflow_skipped_when_conditions_do_not_match(): void
    {
        $workflow = AutomatedWorkflow::create([
            'name' => 'High value order notification',
            'event' => 'order.created',
            'conditions' => [
                ['key' => 'order.total_amount', 'operator' => 'greater_than', 'value' => '100000'],
            ],
            'action' => 'notification',
            'action_config' => [],
            'status' => WorkflowStatus::ACTIVE,
        ]);

        app(WorkflowService::class)->trigger('order.created', [
            'order' => ['total_amount' => 50000],
        ]);

        $workflow->refresh();
        $this->assertSame(0, $workflow->run_count);
    }
}
