@props([
    'statusOptions' => [],
    'categoryOptions' => [],
    'priorityOptions' => [],
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$categoryOptions = is_array($categoryOptions) ? $categoryOptions : [];
$priorityOptions = is_array($priorityOptions) ? $priorityOptions : [];
@endphp

<div class="vestra-feedback__filter-bar">
    <div class="vestra-feedback__filters">
        <div class="vestra-feedback__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-feedback__filter-trigger" aria-haspopup="listbox">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-feedback__filter-dropdown" role="listbox">
                @foreach ($statusOptions as $status)
                    <label class="vestra-feedback__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $status->value }}"
                            class="vestra-feedback__filter-checkbox"
                        />
                        <span class="vestra-feedback__filter-option-label">{{ $status->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-feedback__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-feedback__filter-trigger" aria-haspopup="listbox">
                <span>Category</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-feedback__filter-dropdown" role="listbox">
                @foreach ($categoryOptions as $category)
                    <label class="vestra-feedback__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="categoryFilter"
                            value="{{ $category->value }}"
                            class="vestra-feedback__filter-checkbox"
                        />
                        <span class="vestra-feedback__filter-option-label">{{ $category->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-feedback__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-feedback__filter-trigger" aria-haspopup="listbox">
                <span>Priority</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-feedback__filter-dropdown" role="listbox">
                @foreach ($priorityOptions as $priority)
                    <label class="vestra-feedback__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="priorityFilter"
                            value="{{ $priority->value }}"
                            class="vestra-feedback__filter-checkbox"
                        />
                        <span class="vestra-feedback__filter-option-label">{{ $priority->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-feedback__filter vestra-feedback__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-feedback__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Submitted</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-feedback__filter-dropdown vestra-feedback__filter-dropdown--wide">
                <div class="vestra-feedback__filter-date-fields">
                    <label class="vestra-feedback__filter-field">
                        <span class="vestra-feedback__filter-field-label">From</span>
                        <input type="date" wire:model.live="dateFrom" class="vestra-feedback__filter-input" />
                    </label>
                    <label class="vestra-feedback__filter-field">
                        <span class="vestra-feedback__filter-field-label">Until</span>
                        <input type="date" wire:model.live="dateUntil" class="vestra-feedback__filter-input" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-feedback__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
