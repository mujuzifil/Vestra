@props([
    'show' => true,
    'statusOptions' => [],
    'industryOptions' => [],
    'countryOptions' => [],
    'regionOptions' => [],
    'districtOptions' => [],
    'accountManagerOptions' => [],
    'activeFilterCount' => 0,
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$industryOptions = is_array($industryOptions) ? $industryOptions : [];
$countryOptions = is_array($countryOptions) ? $countryOptions : [];
$regionOptions = is_array($regionOptions) ? $regionOptions : [];
$districtOptions = is_array($districtOptions) ? $districtOptions : [];
$accountManagerOptions = is_array($accountManagerOptions) ? $accountManagerOptions : [];
@endphp

<aside
    class="vestra-companies__filter-panel @if ($show) vestra-companies__filter-panel--open @endif"
    aria-label="Company filters"
    @if (! $show) hidden @endif
>
    <div class="vestra-companies__filter-panel-header">
        <div>
            <h2 class="vestra-companies__filter-panel-title">Filters</h2>
            @if ($activeFilterCount > 0)
                <p class="vestra-companies__filter-panel-subtitle">{{ $activeFilterCount }} active</p>
            @else
                <p class="vestra-companies__filter-panel-subtitle">Refine the company list</p>
            @endif
        </div>
        <button type="button" wire:click="toggleFilterPanel" class="vestra-companies__filter-panel-close" aria-label="Close filters">
            <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
        </button>
    </div>

    <div class="vestra-companies__filter-panel-body">
        <fieldset class="vestra-companies__filter-panel-group">
            <legend class="vestra-companies__filter-panel-legend">Status</legend>
            @foreach ($statusOptions as $status)
                <label class="vestra-companies__filter-option">
                    <input type="checkbox" wire:model="statusFilter" value="{{ $status->value }}" class="vestra-companies__filter-checkbox" />
                    <span class="vestra-companies__filter-option-label">{{ $status->label() }}</span>
                </label>
            @endforeach
        </fieldset>

        @if (! empty($industryOptions))
            <div class="vestra-companies__filter-panel-group">
                <label class="vestra-companies__filter-panel-legend" for="panel-industry">Industry</label>
                <select id="panel-industry" multiple wire:model="industryFilter" class="vestra-companies__filter-panel-select" size="4">
                    @foreach ($industryOptions as $industry)
                        <option value="{{ $industry }}">{{ $industry }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if (! empty($countryOptions))
            <div class="vestra-companies__filter-panel-group">
                <label class="vestra-companies__filter-panel-legend" for="panel-country">Country</label>
                <select id="panel-country" multiple wire:model="countryFilter" class="vestra-companies__filter-panel-select" size="4">
                    @foreach ($countryOptions as $country)
                        <option value="{{ $country }}">{{ $country }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if (! empty($regionOptions))
            <div class="vestra-companies__filter-panel-group">
                <label class="vestra-companies__filter-panel-legend" for="panel-region">Region</label>
                <select id="panel-region" multiple wire:model="regionFilter" class="vestra-companies__filter-panel-select" size="3">
                    @foreach ($regionOptions as $region)
                        <option value="{{ $region }}">{{ $region }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if (! empty($districtOptions))
            <div class="vestra-companies__filter-panel-group">
                <label class="vestra-companies__filter-panel-legend" for="panel-district">District</label>
                <select id="panel-district" multiple wire:model="districtFilter" class="vestra-companies__filter-panel-select" size="3">
                    @foreach ($districtOptions as $district)
                        <option value="{{ $district }}">{{ $district }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if (! empty($accountManagerOptions))
            <div class="vestra-companies__filter-panel-group">
                <label class="vestra-companies__filter-panel-legend" for="panel-manager">Account Manager</label>
                <select id="panel-manager" wire:model="accountManagerFilter" class="vestra-companies__filter-panel-select">
                    <option value="">All account managers</option>
                    @foreach ($accountManagerOptions as $manager)
                        <option value="{{ $manager['id'] }}">{{ $manager['name'] }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="vestra-companies__filter-panel-group">
            <span class="vestra-companies__filter-panel-legend">Date Range</span>
            <label class="vestra-companies__filter-field">
                <span class="vestra-companies__filter-field-label">From</span>
                <input type="date" wire:model="dateFrom" class="vestra-companies__filter-input" />
            </label>
            <label class="vestra-companies__filter-field">
                <span class="vestra-companies__filter-field-label">Until</span>
                <input type="date" wire:model="dateUntil" class="vestra-companies__filter-input" />
            </label>
        </div>

        <div class="vestra-companies__filter-panel-group">
            <span class="vestra-companies__filter-panel-legend">Relationships</span>
            <label class="vestra-companies__filter-option">
                <input type="checkbox" wire:model="hasOpenQuotes" class="vestra-companies__filter-checkbox" />
                <span class="vestra-companies__filter-option-label">Has open quotes</span>
            </label>
            <label class="vestra-companies__filter-option">
                <input type="checkbox" wire:model="hasActiveTickets" class="vestra-companies__filter-checkbox" />
                <span class="vestra-companies__filter-option-label">Has active tickets</span>
            </label>
            <label class="vestra-companies__filter-option">
                <input type="checkbox" wire:model="hasDistributor" class="vestra-companies__filter-checkbox" />
                <span class="vestra-companies__filter-option-label">Has distributor relationship</span>
            </label>
        </div>
    </div>

    <div class="vestra-companies__filter-panel-footer">
        <button type="button" wire:click="resetFilters" class="vestra-button vestra-button--secondary">Reset</button>
        <button type="button" wire:click="applyFilters" class="vestra-button vestra-button--primary">Apply Filters</button>
    </div>
</aside>
