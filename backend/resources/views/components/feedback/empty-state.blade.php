@props([
    'hasFilters' => false,
])

<div class="vestra-feedback__empty">
    <span class="vestra-feedback__empty-icon">
        <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-8 w-8" />
    </span>

    <h4 class="vestra-feedback__empty-title">
        @if ($hasFilters)
            No feedback found
        @else
            No customer feedback yet
        @endif
    </h4>

    <p class="vestra-feedback__empty-description">
        @if ($hasFilters)
            Try adjusting your filters to find what you're looking for.
        @else
            Customer feedback submitted from the website will appear here for review.
        @endif
    </p>
</div>
