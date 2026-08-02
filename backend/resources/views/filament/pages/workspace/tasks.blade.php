@php
$tasks = $this->tasks;
$kpiCards = $this->kpiCards;
$assignees = $this->assignees;
@endphp

<div class="vestra-workspace vestra-tasks">
    <x-tasks.page-header
        title="Tasks"
        description="Manage work, assignments, and priorities across your organisation."
        :tasks="$tasks"
    />

    <section class="vestra-workspace__section" aria-label="Task metrics">
        <x-tasks.kpi-cards :cards="$kpiCards" />
    </section>

    <section class="vestra-workspace__section vestra-tasks__content" aria-label="Task list">
        <div class="vestra-card vestra-tasks__table-card">
            <x-tasks.filter-bar
                :assignees="$assignees"
                :status-options="\App\Enums\TaskStatus::cases()"
                :priority-options="\App\Enums\TaskPriority::cases()"
            />

            @if ($tasks->total() > 0)
                <x-tasks.task-table
                    :tasks="$tasks"
                    :sort-field="$sortField"
                    :sort-direction="$sortDirection"
                />

                <x-tasks.pagination :paginator="$tasks" />
            @else
                <x-tasks.empty-state
                    icon="heroicon-o-check-circle"
                    title="No tasks found"
                    :description="$this->hasActiveFilters()
                        ? 'Try adjusting your filters to find what you are looking for.'
                        : 'Tasks will appear here once they are created. Create your first task to begin managing work.'"
                    action-text="Create task"
                    action-click="openCreateDrawer"
                    :show-action="! $this->hasActiveFilters()"
                />
            @endif
        </div>
    </section>

    <x-tasks.task-form
        :show="$showDrawer"
        :editing-task-id="$editingTaskId"
        :assignees="$assignees"
        :status-options="\App\Enums\TaskStatus::cases()"
        :priority-options="\App\Enums\TaskPriority::cases()"
    />
</div>
