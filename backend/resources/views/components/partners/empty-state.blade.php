@props([
    'hasFilters' => false,
])

<div class="vestra-partners__empty">
    <span class="vestra-partners__empty-icon">
        <x-filament::icon icon="heroicon-o-building-storefront" class="h-8 w-8" />
    </span>

    <h4 class="vestra-partners__empty-title">
        @if ($hasFilters)
            No partners found
        @else
            No active partners yet
        @endif
    </h4>

    <p class="vestra-partners__empty-description">
        @if ($hasFilters)
            Try adjusting your filters to find what you're looking for.
        @else
            Approved distributors will appear here once they are onboarded.
        @endif
    </p>
</div>
