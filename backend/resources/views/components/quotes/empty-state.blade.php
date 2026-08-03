@props([
    'hasFilters' => false,
])

<div class="vestra-quotes__empty">
    <span class="vestra-quotes__empty-icon">
        <x-filament::icon icon="heroicon-o-document-text" class="h-8 w-8" />
    </span>

    <h4 class="vestra-quotes__empty-title">
        @if ($hasFilters)
            No quotes found
        @else
            No quote requests yet
        @endif
    </h4>

    <p class="vestra-quotes__empty-description">
        @if ($hasFilters)
            Try adjusting your filters to find what you're looking for.
        @else
            No quote requests have been submitted yet. Quote requests from the website and internal users will appear here.
        @endif
    </p>
</div>
