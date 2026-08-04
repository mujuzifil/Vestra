@props([
    'hasFilters' => false,
    'canCreate' => false,
    'createUrl' => null,
])

<div class="vestra-blog__empty">
    <x-filament::icon icon="heroicon-o-newspaper" class="vestra-blog__empty-icon" />
    @if ($hasFilters)
        <h3 class="vestra-blog__empty-title">No articles match your filters</h3>
        <p class="vestra-blog__empty-description">Try adjusting or resetting your filters to see results.</p>
        <button type="button" wire:click="resetFilters" class="vestra-button vestra-button--primary">
            Reset Filters
        </button>
    @else
        <h3 class="vestra-blog__empty-title">No articles yet</h3>
        <p class="vestra-blog__empty-description">Articles published to the blog will appear here.</p>
        @if ($canCreate && $createUrl)
            <a href="{{ $createUrl }}" class="vestra-button vestra-button--primary">
                New Article
            </a>
        @endif
    @endif
</div>
