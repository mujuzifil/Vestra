@props([
    'categoryOptions' => [],
    'statusOptions' => [],
    'moduleOptions' => [],
    'userOptions' => [],
    'selectedIds' => [],
])

@php
$categoryOptions = is_array($categoryOptions) ? $categoryOptions : [];
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$moduleOptions = is_array($moduleOptions) ? $moduleOptions : [];
$userOptions = is_array($userOptions) ? $userOptions : [];
$selectedCount = count($selectedIds);
@endphp

<div class="vestra-activity__filter-bar">
    <div class="vestra-activity__search">
        <x-filament::icon icon="heroicon-o-magnifying-glass" class="vestra-activity__search-icon" />
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search activities, users, actions, modules..."
            class="vestra-activity__search-input"
            aria-label="Search activities"
        />
    </div>

    <div class="vestra-activity__filters">
        <div class="vestra-activity__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-activity__filter-trigger">
                <span>Category</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-activity__filter-dropdown">
                @foreach ($categoryOptions as $category)
                    <label class="vestra-activity__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="categoryFilter"
                            value="{{ $category->value }}"
                            class="vestra-activity__filter-checkbox"
                        />
                        <span class="vestra-activity__filter-option-label">{{ $category->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-activity__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-activity__filter-trigger">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-activity__filter-dropdown">
                @foreach ($statusOptions as $status)
                    <label class="vestra-activity__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $status->value }}"
                            class="vestra-activity__filter-checkbox"
                        />
                        <span class="vestra-activity__filter-option-label">{{ $status->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-activity__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-activity__filter-trigger">
                <span>Module</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-activity__filter-dropdown vestra-activity__filter-dropdown--wide">
                @foreach ($moduleOptions as $module)
                    <label class="vestra-activity__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="moduleFilter"
                            value="{{ $module }}"
                            class="vestra-activity__filter-checkbox"
                        />
                        <span class="vestra-activity__filter-option-label">{{ $module }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-activity__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-activity__filter-trigger">
                <span>User</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-activity__filter-dropdown vestra-activity__filter-dropdown--wide">
                <label class="vestra-activity__filter-option">
                    <input type="radio" wire:model.live="userFilter" value="" class="vestra-activity__filter-radio" />
                    <span class="vestra-activity__filter-option-label">All Users</span>
                </label>
                @foreach ($userOptions as $user)
                    <label class="vestra-activity__filter-option">
                        <input type="radio" wire:model.live="userFilter" value="{{ $user['id'] }}" class="vestra-activity__filter-radio" />
                        <span class="vestra-activity__filter-option-label">{{ $user['name'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-activity__filter vestra-activity__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-activity__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Date</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-activity__filter-dropdown vestra-activity__filter-dropdown--wide">
                <div class="vestra-activity__filter-date-fields">
                    <label class="vestra-activity__filter-field">
                        <span class="vestra-activity__filter-field-label">From</span>
                        <input type="date" wire:model.live="dateFrom" class="vestra-activity__filter-input" />
                    </label>
                    <label class="vestra-activity__filter-field">
                        <span class="vestra-activity__filter-field-label">Until</span>
                        <input type="date" wire:model.live="dateUntil" class="vestra-activity__filter-input" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-activity__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>

@if ($selectedCount > 0)
    <div class="vestra-activity__bulk-bar">
        <span class="vestra-activity__bulk-count">{{ $selectedCount }} selected</span>

        <div class="vestra-activity__bulk-actions">
            <button
                type="button"
                wire:click="selectPage(false)"
                class="vestra-button vestra-button--secondary vestra-button--sm"
            >
                Clear Selection
            </button>
        </div>
    </div>
@endif
