<?php

namespace App\Services\Admin;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function queryTasks(array $filters = [], string $sort = 'due_date', string $direction = 'asc'): Builder
    {
        $query = Task::query()
            ->with(['assignee', 'creator', 'related'])
            ->when($filters['search'] ?? null, fn (Builder $q, string $term) => $q->search($term))
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->statusIn($statuses))
            ->when($filters['priority'] ?? null, fn (Builder $q, array $priorities) => $q->priorityIn($priorities))
            ->when($filters['assignee'] ?? null, fn (Builder $q, int $assigneeId) => $q->assignedTo($assigneeId))
            ->when($filters['due_from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('due_date', '>=', $from))
            ->when($filters['due_until'] ?? null, fn (Builder $q, string $until) => $q->whereDate('due_date', '<=', $until))
            ->when($filters['related_type'] ?? null, fn (Builder $q, string $type) => $q->where('related_type', $type));

        return $this->applySorting($query, $sort, $direction);
    }

    public function paginateTasks(array $filters = [], string $sort = 'due_date', string $direction = 'asc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryTasks($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @return array<string, mixed>
     */
    public function getKpiCards(): array
    {
        return Cache::remember('admin.tasks.kpi', 300, function (): array {
            return [
                $this->buildTotalTasksCard(),
                $this->buildCompletedCard(),
                $this->buildInProgressCard(),
                $this->buildOverdueCard(),
            ];
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createTask(User $creator, array $data): Task
    {
        return DB::transaction(function () use ($creator, $data) {
            $task = new Task([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? TaskStatus::NEW->value,
                'priority' => $data['priority'] ?? TaskPriority::MEDIUM->value,
                'assignee_id' => $data['assignee_id'] ?? null,
                'created_by_id' => $creator->id,
                'related_type' => $data['related_type'] ?? null,
                'related_id' => $data['related_id'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'attachment_paths' => $data['attachment_paths'] ?? null,
            ]);

            if ($task->assignee_id !== null && $task->status === TaskStatus::NEW) {
                $task->status = TaskStatus::ASSIGNED;
            }

            $task->save();

            AuditService::log(
                auth()->user(),
                'task.created',
                $task,
                ['title' => $task->title, 'assignee_id' => $task->assignee_id, 'priority' => $task->priority->value]
            );

            if ($task->assignee_id !== null) {
                AuditService::log(
                    auth()->user(),
                    'task.assigned',
                    $task,
                    ['assignee_id' => $task->assignee_id, 'assignee_name' => $task->assignee?->name]
                );
            }

            $this->clearKpiCache();

            return $task;
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateTask(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            $originalAssignee = $task->assignee_id;
            $originalStatus = $task->status;

            $task->fill([
                'title' => $data['title'] ?? $task->title,
                'description' => $data['description'] ?? $task->description,
                'status' => $data['status'] ?? $task->status->value,
                'priority' => $data['priority'] ?? $task->priority->value,
                'assignee_id' => array_key_exists('assignee_id', $data) ? $data['assignee_id'] : $task->assignee_id,
                'related_type' => $data['related_type'] ?? $task->related_type,
                'related_id' => $data['related_id'] ?? $task->related_id,
                'due_date' => $data['due_date'] ?? $task->due_date,
                'internal_notes' => $data['internal_notes'] ?? $task->internal_notes,
                'attachment_paths' => $data['attachment_paths'] ?? $task->attachment_paths,
            ]);

            if ($task->assignee_id !== null && $task->status === TaskStatus::NEW) {
                $task->status = TaskStatus::ASSIGNED;
            }

            if ($task->status === TaskStatus::COMPLETED && $originalStatus !== TaskStatus::COMPLETED) {
                $task->completed_at = now();
            } elseif ($task->status !== TaskStatus::COMPLETED && $originalStatus === TaskStatus::COMPLETED) {
                $task->completed_at = null;
            }

            $task->save();

            AuditService::log(
                auth()->user(),
                'task.updated',
                $task,
                ['title' => $task->title, 'changes' => $task->getChanges()]
            );

            if ($task->assignee_id !== $originalAssignee) {
                AuditService::log(
                    auth()->user(),
                    'task.assigned',
                    $task,
                    ['assignee_id' => $task->assignee_id, 'assignee_name' => $task->assignee?->name]
                );
            }

            $this->clearKpiCache();

            return $task;
        });
    }

    public function deleteTask(Task $task): void
    {
        DB::transaction(function () use ($task) {
            AuditService::log(
                auth()->user(),
                'task.deleted',
                $task,
                ['title' => $task->title]
            );

            $task->delete();
            $this->clearKpiCache();
        });
    }

    public function completeTask(Task $task): void
    {
        DB::transaction(function () use ($task) {
            $task->markCompleted();

            AuditService::log(
                auth()->user(),
                'task.completed',
                $task,
                ['title' => $task->title]
            );

            $this->clearKpiCache();
        });
    }

    public function archiveTask(Task $task): void
    {
        DB::transaction(function () use ($task) {
            $task->status = TaskStatus::ARCHIVED;
            $task->save();

            AuditService::log(
                auth()->user(),
                'task.archived',
                $task,
                ['title' => $task->title]
            );

            $this->clearKpiCache();
        });
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'newest' => $query->orderBy('created_at', $direction),
            'oldest' => $query->orderBy('created_at', $direction),
            'due_date' => $query->orderByRaw('due_date IS NULL, due_date '.$direction),
            'priority' => $query->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low') ".$direction),
            'status' => $query->orderBy('status', $direction),
            'assignee' => $query->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'tasks.assignee_id')
                    ->limit(1),
                $direction
            ),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTotalTasksCard(): array
    {
        $current = Task::query()->count();
        $previous = Task::query()->where('created_at', '<', now()->subDays(7))->count();

        return $this->buildCard('Total Tasks', $current, $previous, 'vs last 7 days', 'heroicon-o-check-circle', 'primary');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCompletedCard(): array
    {
        $current = Task::query()->where('status', TaskStatus::COMPLETED->value)->count();
        $previous = Task::query()
            ->where('status', TaskStatus::COMPLETED->value)
            ->where('completed_at', '<', now()->subDays(7))
            ->count();

        return $this->buildCard('Completed', $current, $previous, 'vs last 7 days', 'heroicon-o-check-badge', 'success');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildInProgressCard(): array
    {
        $current = Task::query()->where('status', TaskStatus::IN_PROGRESS->value)->count();
        $previous = Task::query()
            ->where('status', TaskStatus::IN_PROGRESS->value)
            ->where('updated_at', '<', now()->subDays(7))
            ->count();

        return $this->buildCard('In Progress', $current, $previous, 'vs last 7 days', 'heroicon-o-arrow-path', 'warning');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOverdueCard(): array
    {
        $current = Task::query()->overdue()->count();
        $previous = Task::query()
            ->overdue()
            ->where('updated_at', '<', now()->subDays(7))
            ->count();

        return $this->buildCard('Overdue', $current, $previous, 'vs last 7 days', 'heroicon-o-exclamation-triangle', 'danger');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCard(string $label, float $current, float $previous, string $comparisonLabel, string $icon, string $color): array
    {
        $trend = $this->calculateTrend($current, $previous);

        return [
            'label' => $label,
            'value' => number_format($current),
            'icon' => $icon,
            'color' => $color,
            'trend' => $trend['value'],
            'trend_label' => $trend['label'].' '.$comparisonLabel,
            'trend_positive' => $trend['positive'],
            'trend_available' => $previous > 0 || $current > 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateTrend(float $current, float $previous): array
    {
        if ($previous <= 0 && $current <= 0) {
            return [
                'value' => '0%',
                'label' => 'No change',
                'positive' => true,
            ];
        }

        if ($previous <= 0) {
            return [
                'value' => '+100%',
                'label' => 'Up',
                'positive' => true,
            ];
        }

        $change = (($current - $previous) / $previous) * 100;
        $positive = $change >= 0;

        return [
            'value' => sprintf('%s%.1f%%', $positive ? '+' : '', $change),
            'label' => $positive ? 'Up' : 'Down',
            'positive' => $positive,
        ];
    }

    private function clearKpiCache(): void
    {
        Cache::forget('admin.tasks.kpi');
    }
}
