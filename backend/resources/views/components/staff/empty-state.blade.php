@props([
    'hasFilters' => false,
    'canCreate' => false,
    'createUrl' => null,
])

<div class="vestra-staff__empty">
    <x-filament::icon icon="heroicon-o-users" class="vestra-staff__empty-icon" />
    @if ($hasFilters)
        <h3 class="vestra-staff__empty-title">No staff match your filters</h3>
        <p class="vestra-staff__empty-description">Try adjusting or resetting your filters to see results.</p>
        <button type="button" wire:click="resetFilters" class="vestra-button vestra-button--primary">
            Reset Filters
        </button>
    @else
        <h3 class="vestra-staff__empty-title">No staff yet</h3>
        <p class="vestra-staff__empty-description">staff published to the Staff will appear here.</p>
        @if ($canCreate && $createUrl)
            <a href="{{ $createUrl }}" class="vestra-button vestra-button--primary">
                New staff member
            </a>
        @endif
    @endif
</div>
