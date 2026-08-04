@props([
    'typeOptions' => [],
    'sourceOptions' => [],
    'dateFrom' => null,
    'dateUntil' => null,
])

@php
$typeOptions = is_array($typeOptions) ? $typeOptions : [];
$sourceOptions = is_array($sourceOptions) ? $sourceOptions : [];
@endphp

<div class="vestra-media__filter-bar">
    <div class="vestra-media__filters">
        <div class="vestra-media__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-media__filter-trigger" aria-haspopup="listbox">
                <span>Type</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-media__filter-dropdown" role="listbox">
                @foreach ($typeOptions as $type)
                    <label class="vestra-media__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="typeFilter"
                            value="{{ $type['value'] }}"
                            class="vestra-media__filter-checkbox"
                        />
                        <span class="vestra-media__filter-option-label">{{ $type['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-media__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-media__filter-trigger" aria-haspopup="listbox">
                <span>Source</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-media__filter-dropdown" role="listbox">
                @foreach ($sourceOptions as $source)
                    <label class="vestra-media__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="sourceFilter"
                            value="{{ $source['value'] }}"
                            class="vestra-media__filter-checkbox"
                        />
                        <span class="vestra-media__filter-option-label">{{ $source['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-media__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-media__filter-trigger" aria-haspopup="dialog">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Date</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-media__filter-dropdown vestra-media__filter-dropdown--wide" role="dialog">
                <div class="vestra-media__filter-date-fields">
                    <div class="vestra-media__filter-field">
                        <label class="vestra-media__filter-field-label" for="media-date-from">From</label>
                        <input
                            type="date"
                            id="media-date-from"
                            wire:model.live="dateFrom"
                            class="vestra-media__filter-input"
                        />
                    </div>
                    <div class="vestra-media__filter-field">
                        <label class="vestra-media__filter-field-label" for="media-date-until">Until</label>
                        <input
                            type="date"
                            id="media-date-until"
                            wire:model.live="dateUntil"
                            class="vestra-media__filter-input"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="vestra-media__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-media__filter-trigger" aria-haspopup="listbox">
                <span>Sort</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-media__filter-dropdown" role="listbox">
                <button type="button" wire:click="sortBy('created_at')" class="vestra-media__filter-option vestra-media__filter-option--button">
                    <span class="vestra-media__filter-option-label">Date Uploaded</span>
                </button>
                <button type="button" wire:click="sortBy('name')" class="vestra-media__filter-option vestra-media__filter-option--button">
                    <span class="vestra-media__filter-option-label">File Name</span>
                </button>
                <button type="button" wire:click="sortBy('size')" class="vestra-media__filter-option vestra-media__filter-option--button">
                    <span class="vestra-media__filter-option-label">File Size</span>
                </button>
                <button type="button" wire:click="sortBy('source')" class="vestra-media__filter-option vestra-media__filter-option--button">
                    <span class="vestra-media__filter-option-label">Source</span>
                </button>
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-media__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
