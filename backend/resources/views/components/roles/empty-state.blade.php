@props([
    'hasFilters' => false,
    'canCreate' => false,
    'createUrl' => null,
])

<div class="vestra-roles__empty">
    <x-filament::icon icon="heroicon-o-shield-check" class="vestra-roles__empty-icon" />
    @if ($hasFilters)
        <h3 class="vestra-roles__empty-title">No roles match your filters</h3>
        <p class="vestra-roles__empty-description">Try adjusting or resetting your filters to see results.</p>
        <button type="button" wire:click="resetFilters" class="vestra-button vestra-button--primary">
            Reset Filters
        </button>
    @else
        <h3 class="vestra-roles__empty-title">No roles yet</h3>
        <p class="vestra-roles__empty-description">Roles created for the platform will appear here.</p>
        @if ($canCreate && $createUrl)
            <a href="{{ $createUrl }}" class="vestra-button vestra-button--primary">
                New Role
            </a>
        @endif
    @endif
</div>
