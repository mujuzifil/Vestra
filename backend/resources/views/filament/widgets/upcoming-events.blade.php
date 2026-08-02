<x-filament-widgets::widget class="fi-wi-upcoming-events vestra-card">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-semibold text-[var(--text-heading)]">Calendar</h3>
        <span class="text-sm font-medium text-[var(--text-muted)]">View calendar</span>
    </div>

    <div class="flex flex-col items-center justify-center py-8 text-center">
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--neutral-100)] text-[var(--neutral-400)]">
            <x-filament::icon icon="heroicon-o-calendar" class="h-6 w-6" />
        </span>
        <p class="mt-3 text-sm font-medium text-[var(--text-heading)]">No upcoming events</p>
        <p class="mt-1 text-xs text-[var(--text-muted)] max-w-[16rem]">
            Calendar events will appear here once the events module is enabled.
        </p>
    </div>
</x-filament-widgets::widget>
