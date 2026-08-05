@props([
    'hasFilters' => false,
    'canCreate' => false,
])

<div class="vestra-products__empty">
    <x-filament::icon icon="heroicon-o-shopping-bag" class="vestra-products__empty-icon" />
    @if ($hasFilters)
        <h3 class="vestra-products__empty-title">No products match your filters</h3>
        <p class="vestra-products__empty-description">Try adjusting or resetting your filters to see results.</p>
        <button type="button" wire:click="resetFilters" class="vestra-button vestra-button--primary">
            Reset Filters
        </button>
    @else
        <h3 class="vestra-products__empty-title">No products yet</h3>
        <p class="vestra-products__empty-description">Products added to the catalog will appear here.</p>
        @if ($canCreate)
            <button type="button" wire:click="openCreateModal" class="vestra-button vestra-button--primary">
                Add Product
            </button>
        @endif
    @endif
</div>
