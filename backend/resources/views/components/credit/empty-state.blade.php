@props([
    'hasFilters' => false,
])

<div class="vestra-credit__empty">
    <span class="vestra-credit__empty-icon">
        <x-filament::icon icon="heroicon-o-banknotes" class="h-8 w-8" />
    </span>

    <h4 class="vestra-credit__empty-title">
        @if ($hasFilters)
            No credit accounts found
        @else
            No credit accounts yet
        @endif
    </h4>

    <p class="vestra-credit__empty-description">
        @if ($hasFilters)
            Try adjusting your filters to find what you're looking for.
        @else
            Credit accounts are created automatically once a distributor is onboarded. They will appear here once available.
        @endif
    </p>
</div>
