@props([
    'statusOptions' => [],
    'priorityOptions' => [],
    'countryOptions' => [],
    'regionOptions' => [],
    'assigneeOptions' => [],
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$priorityOptions = is_array($priorityOptions) ? $priorityOptions : [];
$countryOptions = is_array($countryOptions) ? $countryOptions : [];
$regionOptions = is_array($regionOptions) ? $regionOptions : [];
$assigneeOptions = is_array($assigneeOptions) ? $assigneeOptions : [];
@endphp

<div class="vestra-applications__filter-bar">
    <div class="vestra-applications__filters">
        <div class="vestra-applications__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-applications__filter-trigger" aria-haspopup="listbox">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-applications__filter-dropdown" role="listbox">
                @foreach ($statusOptions as $status)
                    <label class="vestra-applications__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $status->value }}"
                            class="vestra-applications__filter-checkbox"
                        />
                        <span class="vestra-applications__filter-option-label">{{ $status->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-applications__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-applications__filter-trigger" aria-haspopup="listbox">
                <span>Priority</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-applications__filter-dropdown" role="listbox">
                @foreach ($priorityOptions as $priority)
                    <label class="vestra-applications__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="priorityFilter"
                            value="{{ $priority->value }}"
                            class="vestra-applications__filter-checkbox"
                        />
                        <span class="vestra-applications__filter-option-label">{{ $priority->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @if (! empty($countryOptions))
            <div class="vestra-applications__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-applications__filter-trigger" aria-haspopup="listbox">
                    <span>Country</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-applications__filter-dropdown" role="listbox">
                    @foreach ($countryOptions as $country)
                        <label class="vestra-applications__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="countryFilter"
                                value="{{ $country }}"
                                class="vestra-applications__filter-checkbox"
                            />
                            <span class="vestra-applications__filter-option-label">{{ $country }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($regionOptions))
            <div class="vestra-applications__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-applications__filter-trigger" aria-haspopup="listbox">
                    <span>Region</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-applications__filter-dropdown" role="listbox">
                    @foreach ($regionOptions as $region)
                        <label class="vestra-applications__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="regionFilter"
                                value="{{ $region }}"
                                class="vestra-applications__filter-checkbox"
                            />
                            <span class="vestra-applications__filter-option-label">{{ $region }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($assigneeOptions))
            <div class="vestra-applications__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-applications__filter-trigger" aria-haspopup="listbox">
                    <span>Assigned To</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-applications__filter-dropdown vestra-applications__filter-dropdown--wide" role="listbox">
                    <label class="vestra-applications__filter-option">
                        <input
                            type="radio"
                            wire:model.live="assignedToFilter"
                            value=""
                            class="vestra-applications__filter-radio"
                        />
                        <span class="vestra-applications__filter-option-label">All administrators</span>
                    </label>
                    @foreach ($assigneeOptions as $assignee)
                        <label class="vestra-applications__filter-option">
                            <input
                                type="radio"
                                wire:model.live="assignedToFilter"
                                value="{{ $assignee['id'] }}"
                                class="vestra-applications__filter-radio"
                            />
                            <span class="vestra-applications__filter-option-label">{{ $assignee['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="vestra-applications__filter vestra-applications__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-applications__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Submitted</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-applications__filter-dropdown vestra-applications__filter-dropdown--wide">
                <div class="vestra-applications__filter-date-fields">
                    <label class="vestra-applications__filter-field">
                        <span class="vestra-applications__filter-field-label">From</span>
                        <input type="date" wire:model.live="dateFrom" class="vestra-applications__filter-input" />
                    </label>
                    <label class="vestra-applications__filter-field">
                        <span class="vestra-applications__filter-field-label">Until</span>
                        <input type="date" wire:model.live="dateUntil" class="vestra-applications__filter-input" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-applications__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
