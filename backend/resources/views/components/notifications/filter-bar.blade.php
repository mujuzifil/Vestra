@props([
    'priorityOptions' => [],
    'categoryOptions' => [],
    'typeOptions' => [],
    'selectedIds' => [],
])

@php
$priorityOptions = is_array($priorityOptions) ? $priorityOptions : [];
$categoryOptions = is_array($categoryOptions) ? $categoryOptions : [];
$typeOptions = is_array($typeOptions) ? $typeOptions : [];
$selectedCount = count($selectedIds);
@endphp

<div class="vestra-notifications__filter-bar">
    <div class="vestra-notifications__search">
        <x-filament::icon icon="heroicon-o-magnifying-glass" class="vestra-notifications__search-icon" />
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search notifications..."
            class="vestra-notifications__search-input"
            aria-label="Search notifications"
        />
    </div>

    <div class="vestra-notifications__filters">
        <div class="vestra-notifications__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-notifications__filter-trigger">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-notifications__filter-dropdown">
                <label class="vestra-notifications__filter-option">
                    <input type="radio" wire:model.live="statusFilter" value="" class="vestra-notifications__filter-radio" />
                    <span class="vestra-notifications__filter-option-label">All</span>
                </label>
                <label class="vestra-notifications__filter-option">
                    <input type="radio" wire:model.live="statusFilter" value="unread" class="vestra-notifications__filter-radio" />
                    <span class="vestra-notifications__filter-option-label">Unread</span>
                </label>
                <label class="vestra-notifications__filter-option">
                    <input type="radio" wire:model.live="statusFilter" value="read" class="vestra-notifications__filter-radio" />
                    <span class="vestra-notifications__filter-option-label">Read</span>
                </label>
            </div>
        </div>

        <div class="vestra-notifications__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-notifications__filter-trigger">
                <span>Priority</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-notifications__filter-dropdown">
                @foreach ($priorityOptions as $priority)
                    <label class="vestra-notifications__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="priorityFilter"
                            value="{{ $priority->value }}"
                            class="vestra-notifications__filter-checkbox"
                        />
                        <span class="vestra-notifications__filter-option-label">{{ $priority->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-notifications__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-notifications__filter-trigger">
                <span>Category</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-notifications__filter-dropdown">
                @foreach ($categoryOptions as $category)
                    <label class="vestra-notifications__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="categoryFilter"
                            value="{{ $category->value }}"
                            class="vestra-notifications__filter-checkbox"
                        />
                        <span class="vestra-notifications__filter-option-label">{{ $category->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-notifications__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-notifications__filter-trigger">
                <span>Type</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-notifications__filter-dropdown vestra-notifications__filter-dropdown--wide">
                @foreach ($typeOptions as $type)
                    <label class="vestra-notifications__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="typeFilter"
                            value="{{ $type->value }}"
                            class="vestra-notifications__filter-checkbox"
                        />
                        <span class="vestra-notifications__filter-option-label">{{ $type->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-notifications__filter vestra-notifications__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-notifications__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Date</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-notifications__filter-dropdown vestra-notifications__filter-dropdown--wide">
                <div class="vestra-notifications__filter-date-fields">
                    <label class="vestra-notifications__filter-field">
                        <span class="vestra-notifications__filter-field-label">From</span>
                        <input type="date" wire:model.live="dateFrom" class="vestra-notifications__filter-input" />
                    </label>
                    <label class="vestra-notifications__filter-field">
                        <span class="vestra-notifications__filter-field-label">Until</span>
                        <input type="date" wire:model.live="dateUntil" class="vestra-notifications__filter-input" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-notifications__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>

@if ($selectedCount > 0)
    <div class="vestra-notifications__bulk-bar">
        <span class="vestra-notifications__bulk-count">{{ $selectedCount }} selected</span>

        <div class="vestra-notifications__bulk-actions">
            <button
                type="button"
                wire:click="bulkMarkRead"
                class="vestra-button vestra-button--secondary vestra-button--sm"
            >
                Mark Read
            </button>

            <button
                type="button"
                wire:click="bulkMarkUnread"
                class="vestra-button vestra-button--secondary vestra-button--sm"
            >
                Mark Unread
            </button>

            <button
                type="button"
                wire:click="bulkDelete"
                class="vestra-button vestra-button--danger vestra-button--sm"
            >
                Delete
            </button>
        </div>
    </div>
@endif
