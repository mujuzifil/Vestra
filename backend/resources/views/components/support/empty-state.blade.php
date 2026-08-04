@props(['hasFilters' => false])

<div class="vestra-support__empty">
    <x-filament::icon icon="heroicon-o-lifebuoy" class="vestra-support__empty-icon" />
    @if ($hasFilters)
        <h3 class="vestra-support__empty-title">No tickets match your filters</h3>
        <p class="vestra-support__empty-description">Try adjusting or resetting your filters to see results.</p>
        <button type="button" wire:click="resetFilters" class="vestra-button vestra-button--primary">
            Reset Filters
        </button>
    @else
        <h3 class="vestra-support__empty-title">No support tickets yet</h3>
        <p class="vestra-support__empty-description">Support tickets submitted by customers will appear here.</p>
    @endif
</div>
