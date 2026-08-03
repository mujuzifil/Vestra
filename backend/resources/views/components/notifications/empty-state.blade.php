@props([
    'hasFilters' => false,
])

<div class="vestra-notifications__empty">
    <span class="vestra-notifications__empty-icon">
        <x-filament::icon icon="heroicon-o-bell-slash" class="h-8 w-8" />
    </span>

    <h4 class="vestra-notifications__empty-title">
        @if ($hasFilters)
            No notifications found
        @else
            You're all caught up
        @endif
    </h4>

    <p class="vestra-notifications__empty-description">
        @if ($hasFilters)
            Try adjusting your filters to find what you're looking for.
        @else
            New notifications will appear here as activity occurs across the platform.
        @endif
    </p>
</div>
