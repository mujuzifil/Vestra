<x-filament-widgets::widget class="fi-wi-upcoming-events vestra-card">
    <div class="vestra-card-header">
        <h3 class="vestra-card-title">Calendar</h3>
        <span class="text-sm font-medium text-[var(--text-muted)]">View calendar</span>
    </div>

    <x-admin.empty-state
        icon="heroicon-o-calendar"
        title="No upcoming events"
        description="Calendar events will appear here once the events module is enabled."
    />
</x-filament-widgets::widget>
