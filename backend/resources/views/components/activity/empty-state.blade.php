@props([
    'hasFilters' => false,
])

<div class="vestra-activity__empty">
    <span class="vestra-activity__empty-icon">
        <x-filament::icon icon="heroicon-o-bolt-slash" class="h-8 w-8" />
    </span>

    <h4 class="vestra-activity__empty-title">
        @if ($hasFilters)
            No activities found
        @else
            No activity yet
        @endif
    </h4>

    <p class="vestra-activity__empty-description">
        @if ($hasFilters)
            Try adjusting your filters to find what you're looking for.
        @else
            Activities from across the platform will appear here as they happen.
        @endif
    </p>
</div>
