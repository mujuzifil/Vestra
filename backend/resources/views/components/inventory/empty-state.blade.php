@props(['hasFilters' => false])

<div class="vestra-inventory__empty">
    <x-filament::icon icon="heroicon-o-cube-transparent" class="vestra-inventory__empty-icon" />
    @if ($hasFilters)
        <h3 class="vestra-inventory__empty-title">No stock lines match your filters</h3>
        <p class="vestra-inventory__empty-description">Try adjusting or resetting your filters to see results.</p>
        <button type="button" wire:click="resetFilters" class="vestra-button vestra-button--primary">
            Reset Filters
        </button>
    @else
        <h3 class="vestra-inventory__empty-title">No inventory records yet</h3>
        <p class="vestra-inventory__empty-description">Warehouse stock levels will appear here once products are stocked.</p>
    @endif
</div>
