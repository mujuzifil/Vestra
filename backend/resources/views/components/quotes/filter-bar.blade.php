@props([
    'statusOptions' => [],
    'priorityOptions' => [],
    'districtOptions' => [],
    'cityOptions' => [],
    'salesRepOptions' => [],
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$priorityOptions = is_array($priorityOptions) ? $priorityOptions : [];
$districtOptions = is_array($districtOptions) ? $districtOptions : [];
$cityOptions = is_array($cityOptions) ? $cityOptions : [];
$salesRepOptions = is_array($salesRepOptions) ? $salesRepOptions : [];
@endphp

<div class="vestra-quotes__filter-bar">
    <div class="vestra-quotes__filters">
        <div class="vestra-quotes__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-quotes__filter-trigger" aria-haspopup="listbox">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-quotes__filter-dropdown" role="listbox">
                @foreach ($statusOptions as $status)
                    <label class="vestra-quotes__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $status->value }}"
                            class="vestra-quotes__filter-checkbox"
                        />
                        <span class="vestra-quotes__filter-option-label">{{ $status->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-quotes__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-quotes__filter-trigger" aria-haspopup="listbox">
                <span>Priority</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-quotes__filter-dropdown" role="listbox">
                @foreach ($priorityOptions as $priority)
                    <label class="vestra-quotes__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="priorityFilter"
                            value="{{ $priority->value }}"
                            class="vestra-quotes__filter-checkbox"
                        />
                        <span class="vestra-quotes__filter-option-label">{{ $priority->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @if (! empty($salesRepOptions))
            <div class="vestra-quotes__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-quotes__filter-trigger" aria-haspopup="listbox">
                    <span>Sales Rep</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-quotes__filter-dropdown vestra-quotes__filter-dropdown--wide" role="listbox">
                    <label class="vestra-quotes__filter-option">
                        <input
                            type="radio"
                            wire:model.live="assignedToFilter"
                            value=""
                            class="vestra-quotes__filter-radio"
                        />
                        <span class="vestra-quotes__filter-option-label">All sales reps</span>
                    </label>
                    @foreach ($salesRepOptions as $rep)
                        <label class="vestra-quotes__filter-option">
                            <input
                                type="radio"
                                wire:model.live="assignedToFilter"
                                value="{{ $rep['id'] }}"
                                class="vestra-quotes__filter-radio"
                            />
                            <span class="vestra-quotes__filter-option-label">{{ $rep['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($districtOptions))
            <div class="vestra-quotes__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-quotes__filter-trigger" aria-haspopup="listbox">
                    <span>District</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-quotes__filter-dropdown" role="listbox">
                    @foreach ($districtOptions as $district)
                        <label class="vestra-quotes__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="districtFilter"
                                value="{{ $district }}"
                                class="vestra-quotes__filter-checkbox"
                            />
                            <span class="vestra-quotes__filter-option-label">{{ $district }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($cityOptions))
            <div class="vestra-quotes__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-quotes__filter-trigger" aria-haspopup="listbox">
                    <span>City</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-quotes__filter-dropdown" role="listbox">
                    @foreach ($cityOptions as $city)
                        <label class="vestra-quotes__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="cityFilter"
                                value="{{ $city }}"
                                class="vestra-quotes__filter-checkbox"
                            />
                            <span class="vestra-quotes__filter-option-label">{{ $city }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="vestra-quotes__filter vestra-quotes__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-quotes__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Created</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-quotes__filter-dropdown vestra-quotes__filter-dropdown--wide">
                <div class="vestra-quotes__filter-date-fields">
                    <label class="vestra-quotes__filter-field">
                        <span class="vestra-quotes__filter-field-label">From</span>
                        <input type="date" wire:model.live="dateFrom" class="vestra-quotes__filter-input" />
                    </label>
                    <label class="vestra-quotes__filter-field">
                        <span class="vestra-quotes__filter-field-label">Until</span>
                        <input type="date" wire:model.live="dateUntil" class="vestra-quotes__filter-input" />
                    </label>
                </div>
            </div>
        </div>

        <div class="vestra-quotes__filter vestra-quotes__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-quotes__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4" />
                <span>Expiry</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-quotes__filter-dropdown vestra-quotes__filter-dropdown--wide">
                <div class="vestra-quotes__filter-date-fields">
                    <label class="vestra-quotes__filter-field">
                        <span class="vestra-quotes__filter-field-label">Close from</span>
                        <input type="date" wire:model.live="closeFrom" class="vestra-quotes__filter-input" />
                    </label>
                    <label class="vestra-quotes__filter-field">
                        <span class="vestra-quotes__filter-field-label">Close until</span>
                        <input type="date" wire:model.live="closeUntil" class="vestra-quotes__filter-input" />
                    </label>
                </div>
            </div>
        </div>

        <div class="vestra-quotes__filter vestra-quotes__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-quotes__filter-trigger">
                <span>Value</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-quotes__filter-dropdown vestra-quotes__filter-dropdown--wide">
                <div class="vestra-quotes__filter-date-fields">
                    <label class="vestra-quotes__filter-field">
                        <span class="vestra-quotes__filter-field-label">Min amount</span>
                        <input type="number" min="0" step="0.01" wire:model.live.debounce.400ms="minValue" class="vestra-quotes__filter-input" placeholder="0" />
                    </label>
                    <label class="vestra-quotes__filter-field">
                        <span class="vestra-quotes__filter-field-label">Max amount</span>
                        <input type="number" min="0" step="0.01" wire:model.live.debounce.400ms="maxValue" class="vestra-quotes__filter-input" placeholder="Any" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-quotes__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
