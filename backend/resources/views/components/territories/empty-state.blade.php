@props([
    'hasFilters' => false,
])

<div class="vestra-territories__empty">
    <span class="vestra-territories__empty-icon">
        <x-filament::icon icon="heroicon-o-map" class="h-8 w-8" />
    </span>

    <h4 class="vestra-territories__empty-title">
        @if ($hasFilters)
            No distributor coverage found
        @else
            No distributor coverage configured
        @endif
    </h4>

    <p class="vestra-territories__empty-description">
        @if ($hasFilters)
            Try adjusting your filters to find what you're looking for.
        @else
            Approved distributors appear here automatically. Administrators can assign coverage areas from the distributor profile.
        @endif
    </p>
</div>
