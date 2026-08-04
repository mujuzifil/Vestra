@props(['hasFilters' => false])

<div class="vestra-enquiries__empty-state">
    <x-filament::icon icon="heroicon-o-envelope" class="vestra-enquiries__empty-icon" />

    @if ($hasFilters)
        <h3 class="vestra-enquiries__empty-title">No enquiries match your filters</h3>
        <p class="vestra-enquiries__empty-description">Try adjusting or clearing your filters to find enquiries.</p>
        <button type="button" wire:click="resetFilters" class="vestra-button vestra-button--secondary">
            Clear Filters
        </button>
    @else
        <h3 class="vestra-enquiries__empty-title">No enquiries yet</h3>
        <p class="vestra-enquiries__empty-description">Customer enquiries will appear here once they submit the contact form.</p>
    @endif
</div>
