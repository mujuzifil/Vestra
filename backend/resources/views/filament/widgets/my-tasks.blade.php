<x-filament-widgets::widget class="fi-wi-my-tasks vestra-card">
    <div class="vestra-card-header">
        <h3 class="vestra-card-title">My Tasks</h3>
        <span class="text-sm font-medium text-[var(--text-muted)]">View all</span>
    </div>

    <x-admin.empty-state
        icon="heroicon-o-check-circle"
        title="No active tasks"
        description="Tasks assigned to you will appear here once the task management module is enabled."
    />
</x-filament-widgets::widget>
