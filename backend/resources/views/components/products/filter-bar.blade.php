@props([
    'statusOptions' => [],
    'categoryOptions' => [],
    'stockOptions' => [],
    'featuredOptions' => [],
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$categoryOptions = is_array($categoryOptions) ? $categoryOptions : [];
$stockOptions = is_array($stockOptions) ? $stockOptions : [];
$featuredOptions = is_array($featuredOptions) ? $featuredOptions : [];
@endphp

<div class="vestra-products__filter-bar">
    <div class="vestra-products__filters">
        <div class="vestra-products__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-products__filter-trigger" aria-haspopup="listbox">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-products__filter-dropdown" role="listbox">
                @foreach ($statusOptions as $status)
                    <label class="vestra-products__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $status['value'] ?? $status }}"
                            class="vestra-products__filter-checkbox"
                        />
                        <span class="vestra-products__filter-option-label">{{ $status['label'] ?? ucfirst(str_replace('_', ' ', $status)) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @if (! empty($categoryOptions))
            <div class="vestra-products__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-products__filter-trigger" aria-haspopup="listbox">
                    <span>Category</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-products__filter-dropdown vestra-products__filter-dropdown--wide" role="listbox">
                    @foreach ($categoryOptions as $category)
                        <label class="vestra-products__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="categoryFilter"
                                value="{{ $category['id'] }}"
                                class="vestra-products__filter-checkbox"
                            />
                            <span class="vestra-products__filter-option-label">{{ $category['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="vestra-products__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-products__filter-trigger" aria-haspopup="listbox">
                <span>Stock</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-products__filter-dropdown" role="listbox">
                <label class="vestra-products__filter-option">
                    <input
                        type="radio"
                        wire:model.live="stockFilter"
                        value=""
                        class="vestra-products__filter-radio"
                    />
                    <span class="vestra-products__filter-option-label">All stock levels</span>
                </label>
                @foreach ($stockOptions as $option)
                    <label class="vestra-products__filter-option">
                        <input
                            type="radio"
                            wire:model.live="stockFilter"
                            value="{{ $option['value'] }}"
                            class="vestra-products__filter-radio"
                        />
                        <span class="vestra-products__filter-option-label">{{ $option['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-products__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-products__filter-trigger" aria-haspopup="listbox">
                <span>Featured</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-products__filter-dropdown" role="listbox">
                <label class="vestra-products__filter-option">
                    <input
                        type="radio"
                        wire:model.live="featuredFilter"
                        value=""
                        class="vestra-products__filter-radio"
                    />
                    <span class="vestra-products__filter-option-label">All products</span>
                </label>
                @foreach ($featuredOptions as $option)
                    <label class="vestra-products__filter-option">
                        <input
                            type="radio"
                            wire:model.live="featuredFilter"
                            value="{{ $option['value'] }}"
                            class="vestra-products__filter-radio"
                        />
                        <span class="vestra-products__filter-option-label">{{ $option['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-products__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
