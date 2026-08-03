@props([
    'statusOptions' => [],
    'industryOptions' => [],
    'countryOptions' => [],
    'regionOptions' => [],
    'districtOptions' => [],
    'accountManagerOptions' => [],
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$industryOptions = is_array($industryOptions) ? $industryOptions : [];
$countryOptions = is_array($countryOptions) ? $countryOptions : [];
$regionOptions = is_array($regionOptions) ? $regionOptions : [];
$districtOptions = is_array($districtOptions) ? $districtOptions : [];
$accountManagerOptions = is_array($accountManagerOptions) ? $accountManagerOptions : [];
@endphp

<div class="vestra-companies__filter-bar">
    <div class="vestra-companies__filters">
        <div class="vestra-companies__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-companies__filter-trigger">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-companies__filter-dropdown">
                @foreach ($statusOptions as $status)
                    <label class="vestra-companies__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $status->value }}"
                            class="vestra-companies__filter-checkbox"
                        />
                        <span class="vestra-companies__filter-option-label">{{ $status->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @if (! empty($industryOptions))
            <div class="vestra-companies__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-companies__filter-trigger">
                    <span>Industry</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-companies__filter-dropdown">
                    @foreach ($industryOptions as $industry)
                        <label class="vestra-companies__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="industryFilter"
                                value="{{ $industry }}"
                                class="vestra-companies__filter-checkbox"
                            />
                            <span class="vestra-companies__filter-option-label">{{ $industry }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($countryOptions))
            <div class="vestra-companies__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-companies__filter-trigger">
                    <span>Country</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-companies__filter-dropdown">
                    @foreach ($countryOptions as $country)
                        <label class="vestra-companies__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="countryFilter"
                                value="{{ $country }}"
                                class="vestra-companies__filter-checkbox"
                            />
                            <span class="vestra-companies__filter-option-label">{{ $country }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($regionOptions))
            <div class="vestra-companies__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-companies__filter-trigger">
                    <span>Region</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-companies__filter-dropdown">
                    @foreach ($regionOptions as $region)
                        <label class="vestra-companies__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="regionFilter"
                                value="{{ $region }}"
                                class="vestra-companies__filter-checkbox"
                            />
                            <span class="vestra-companies__filter-option-label">{{ $region }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($districtOptions))
            <div class="vestra-companies__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-companies__filter-trigger">
                    <span>District</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-companies__filter-dropdown">
                    @foreach ($districtOptions as $district)
                        <label class="vestra-companies__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="districtFilter"
                                value="{{ $district }}"
                                class="vestra-companies__filter-checkbox"
                            />
                            <span class="vestra-companies__filter-option-label">{{ $district }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($accountManagerOptions))
            <div class="vestra-companies__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-companies__filter-trigger">
                    <span>Account Manager</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-companies__filter-dropdown vestra-companies__filter-dropdown--wide">
                    <label class="vestra-companies__filter-option">
                        <input
                            type="radio"
                            wire:model.live="accountManagerFilter"
                            value=""
                            class="vestra-companies__filter-radio"
                        />
                        <span class="vestra-companies__filter-option-label">All account managers</span>
                    </label>
                    @foreach ($accountManagerOptions as $manager)
                        <label class="vestra-companies__filter-option">
                            <input
                                type="radio"
                                wire:model.live="accountManagerFilter"
                                value="{{ $manager['id'] }}"
                                class="vestra-companies__filter-radio"
                            />
                            <span class="vestra-companies__filter-option-label">{{ $manager['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="vestra-companies__filter vestra-companies__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-companies__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Date</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-companies__filter-dropdown vestra-companies__filter-dropdown--wide">
                <div class="vestra-companies__filter-date-fields">
                    <label class="vestra-companies__filter-field">
                        <span class="vestra-companies__filter-field-label">From</span>
                        <input type="date" wire:model.live="dateFrom" class="vestra-companies__filter-input" />
                    </label>
                    <label class="vestra-companies__filter-field">
                        <span class="vestra-companies__filter-field-label">Until</span>
                        <input type="date" wire:model.live="dateUntil" class="vestra-companies__filter-input" />
                    </label>
                </div>
            </div>
        </div>

        <label class="vestra-companies__filter-toggle">
            <input type="checkbox" wire:model.live="hasOpenQuotes" class="vestra-companies__filter-toggle-input" />
            <span class="vestra-companies__filter-toggle-label">Open quotes</span>
        </label>

        <label class="vestra-companies__filter-toggle">
            <input type="checkbox" wire:model.live="hasActiveTickets" class="vestra-companies__filter-toggle-input" />
            <span class="vestra-companies__filter-toggle-label">Active tickets</span>
        </label>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-companies__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
