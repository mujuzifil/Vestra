@props([
    'warehouseOptions' => [],
    'categoryOptions' => [],
    'stockStatusOptions' => [],
])

@php
$warehouseOptions = is_array($warehouseOptions) ? $warehouseOptions : [];
$categoryOptions = is_array($categoryOptions) ? $categoryOptions : [];
$stockStatusOptions = is_array($stockStatusOptions) ? $stockStatusOptions : [];

$statusLabels = [
    'in' => 'In Stock',
    'low' => 'Low Stock',
    'out' => 'Out of Stock',
];
@endphp

<div class="vestra-inventory__filter-bar">
    <div class="vestra-inventory__filters">
        <div class="vestra-inventory__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-inventory__filter-trigger" aria-haspopup="listbox">
                <span>Warehouse</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-inventory__filter-dropdown vestra-inventory__filter-dropdown--wide" role="listbox">
                @forelse ($warehouseOptions as $warehouse)
                    <label class="vestra-inventory__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="warehouseFilter"
                            value="{{ $warehouse['id'] }}"
                            class="vestra-inventory__filter-checkbox"
                        />
                        <span class="vestra-inventory__filter-option-label">
                            {{ $warehouse['name'] }}
                            @if (! empty($warehouse['code']))
                                <span class="vestra-inventory__row-meta">({{ $warehouse['code'] }})</span>
                            @endif
                        </span>
                    </label>
                @empty
                    <p class="vestra-inventory__filter-option-label" style="padding: 0.5rem 0.75rem;">No warehouses</p>
                @endforelse
            </div>
        </div>

        <div class="vestra-inventory__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-inventory__filter-trigger" aria-haspopup="listbox">
                <span>Category</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-inventory__filter-dropdown" role="listbox">
                @forelse ($categoryOptions as $category)
                    <label class="vestra-inventory__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="categoryFilter"
                            value="{{ $category['id'] }}"
                            class="vestra-inventory__filter-checkbox"
                        />
                        <span class="vestra-inventory__filter-option-label">{{ $category['name'] }}</span>
                    </label>
                @empty
                    <p class="vestra-inventory__filter-option-label" style="padding: 0.5rem 0.75rem;">No categories</p>
                @endforelse
            </div>
        </div>

        <div class="vestra-inventory__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-inventory__filter-trigger" aria-haspopup="listbox">
                <span>Stock Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-inventory__filter-dropdown" role="listbox">
                @foreach ($stockStatusOptions as $status)
                    <label class="vestra-inventory__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="stockStatusFilter"
                            value="{{ $status }}"
                            class="vestra-inventory__filter-checkbox"
                        />
                        <span class="vestra-inventory__filter-option-label">{{ $statusLabels[$status] ?? ucfirst($status) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-inventory__filter vestra-inventory__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-inventory__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Updated</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-inventory__filter-dropdown vestra-inventory__filter-dropdown--wide">
                <div class="vestra-inventory__filter-date-fields">
                    <label class="vestra-inventory__filter-field">
                        <span class="vestra-inventory__filter-field-label">From</span>
                        <input type="date" wire:model.live="dateFrom" class="vestra-inventory__filter-input" />
                    </label>
                    <label class="vestra-inventory__filter-field">
                        <span class="vestra-inventory__filter-field-label">Until</span>
                        <input type="date" wire:model.live="dateUntil" class="vestra-inventory__filter-input" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-inventory__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
