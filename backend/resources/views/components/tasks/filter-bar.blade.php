@props([
    'assignees' => [],
    'statusOptions' => [],
    'priorityOptions' => [],
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$priorityOptions = is_array($priorityOptions) ? $priorityOptions : [];
@endphp

<div class="vestra-tasks__filter-bar">
    <div class="vestra-tasks__search">
        <x-filament::icon icon="heroicon-o-magnifying-glass" class="vestra-tasks__search-icon" />
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search tasks..."
            class="vestra-tasks__search-input"
            aria-label="Search tasks"
        />
    </div>

    <div class="vestra-tasks__filters">
        <div class="vestra-tasks__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-tasks__filter-trigger">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-tasks__filter-dropdown">
                @foreach ($statusOptions as $status)
                    <label class="vestra-tasks__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $status->value }}"
                            class="vestra-tasks__filter-checkbox"
                        />
                        <span class="vestra-tasks__filter-option-label">{{ $status->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-tasks__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-tasks__filter-trigger">
                <span>Priority</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-tasks__filter-dropdown">
                @foreach ($priorityOptions as $priority)
                    <label class="vestra-tasks__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="priorityFilter"
                            value="{{ $priority->value }}"
                            class="vestra-tasks__filter-checkbox"
                        />
                        <span class="vestra-tasks__filter-option-label">{{ $priority->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-tasks__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-tasks__filter-trigger">
                <span>Assignee</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-tasks__filter-dropdown">
                <label class="vestra-tasks__filter-option">
                    <input
                        type="radio"
                        wire:model.live="assigneeFilter"
                        value=""
                        class="vestra-tasks__filter-checkbox"
                    />
                    <span class="vestra-tasks__filter-option-label">All assignees</span>
                </label>
                @foreach ($assignees as $assignee)
                    <label class="vestra-tasks__filter-option">
                        <input
                            type="radio"
                            wire:model.live="assigneeFilter"
                            value="{{ $assignee['id'] }}"
                            class="vestra-tasks__filter-checkbox"
                        />
                        <span class="vestra-tasks__filter-option-label">{{ $assignee['name'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-tasks__filter vestra-tasks__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-tasks__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Due Date</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-tasks__filter-dropdown vestra-tasks__filter-dropdown--wide">
                <div class="vestra-tasks__filter-date-fields">
                    <label class="vestra-tasks__filter-field">
                        <span class="vestra-tasks__filter-field-label">From</span>
                        <input type="date" wire:model.live="dueFrom" class="vestra-tasks__filter-input" />
                    </label>
                    <label class="vestra-tasks__filter-field">
                        <span class="vestra-tasks__filter-field-label">Until</span>
                        <input type="date" wire:model.live="dueUntil" class="vestra-tasks__filter-input" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-tasks__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
