@props([
    'hasFilters' => false,
])

<div class="vestra-applications__empty">
    <span class="vestra-applications__empty-icon">
        <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-8 w-8" />
    </span>

    <h4 class="vestra-applications__empty-title">
        @if ($hasFilters)
            No applications found
        @else
            No distributor applications yet
        @endif
    </h4>

    <p class="vestra-applications__empty-description">
        @if ($hasFilters)
            Try adjusting your filters to find what you're looking for.
        @else
            Distributor applications submitted from the website will appear here for review.
        @endif
    </p>
</div>
