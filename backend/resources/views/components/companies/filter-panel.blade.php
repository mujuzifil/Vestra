@props([
    'show' => true,
    'statusOptions' => [],
    'industryOptions' => [],
    'countryOptions' => [],
    'regionOptions' => [],
    'districtOptions' => [],
    'accountManagerOptions' => [],
    'industryFilter' => [],
    'countryFilter' => [],
    'regionFilter' => [],
    'districtFilter' => [],
    'statusFilter' => [],
    'datePreset' => '',
    'activeFilterCount' => 0,
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$industryOptions = is_array($industryOptions) ? $industryOptions : [];
$countryOptions = is_array($countryOptions) ? $countryOptions : [];
$regionOptions = is_array($regionOptions) ? $regionOptions : [];
$districtOptions = is_array($districtOptions) ? $districtOptions : [];
$accountManagerOptions = is_array($accountManagerOptions) ? $accountManagerOptions : [];
$industryFilter = is_array($industryFilter) ? $industryFilter : [];
$countryFilter = is_array($countryFilter) ? $countryFilter : [];
$regionFilter = is_array($regionFilter) ? $regionFilter : [];
$districtFilter = is_array($districtFilter) ? $districtFilter : [];
$statusFilter = is_array($statusFilter) ? $statusFilter : [];

$selectedIndustry = $industryFilter[0] ?? '';
$selectedCountry = $countryFilter[0] ?? '';
$selectedRegion = $regionFilter[0] ?? '';
$selectedDistrict = $districtFilter[0] ?? '';
$allStatusSelected = count($statusFilter) === 0;
@endphp

<aside
    class="vestra-companies__filter-panel @if ($show) vestra-companies__filter-panel--open @endif"
    aria-label="Company filters"
>
    <div class="vestra-companies__filter-panel-header">
        <h2 class="vestra-companies__filter-panel-title">Filters</h2>
        <button type="button" wire:click="resetFilters" class="vestra-companies__filter-panel-clear">
            Clear all
        </button>
    </div>

    <div class="vestra-companies__filter-panel-body">
        <fieldset class="vestra-companies__filter-panel-group">
            <legend class="vestra-companies__filter-panel-legend">Status</legend>
            <label class="vestra-companies__filter-option">
                <input
                    type="checkbox"
                    class="vestra-companies__filter-checkbox"
                    wire:click.prevent="clearStatusFilter"
                    @checked($allStatusSelected)
                />
                <span class="vestra-companies__filter-option-label">All</span>
            </label>
            @foreach ($statusOptions as $status)
                <label class="vestra-companies__filter-option">
                    <input type="checkbox" wire:model.live="statusFilter" value="{{ $status->value }}" class="vestra-companies__filter-checkbox" />
                    <span class="vestra-companies__filter-option-label">{{ $status->label() }}</span>
                </label>
            @endforeach
        </fieldset>

        @if (! empty($industryOptions))
            <div class="vestra-companies__filter-panel-group">
                <label class="vestra-companies__filter-panel-legend" for="panel-industry">Industry</label>
                <select
                    id="panel-industry"
                    class="vestra-companies__filter-panel-select"
                    wire:change="$set('industryFilter', $event.target.value ? [$event.target.value] : [])"
                >
                    <option value="" @selected($selectedIndustry === '')>All industries</option>
                    @foreach ($industryOptions as $industry)
                        <option value="{{ $industry }}" @selected($selectedIndustry === $industry)>{{ $industry }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if (! empty($countryOptions))
            <div class="vestra-companies__filter-panel-group">
                <label class="vestra-companies__filter-panel-legend" for="panel-country">Country</label>
                <select
                    id="panel-country"
                    class="vestra-companies__filter-panel-select"
                    wire:change="$set('countryFilter', $event.target.value ? [$event.target.value] : [])"
                >
                    <option value="" @selected($selectedCountry === '')>All countries</option>
                    @foreach ($countryOptions as $country)
                        <option value="{{ $country }}" @selected($selectedCountry === $country)>{{ $country }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if (! empty($accountManagerOptions))
            <div class="vestra-companies__filter-panel-group">
                <label class="vestra-companies__filter-panel-legend" for="panel-manager">Account Manager</label>
                <select id="panel-manager" wire:model.live="accountManagerFilter" class="vestra-companies__filter-panel-select">
                    <option value="">All account managers</option>
                    @foreach ($accountManagerOptions as $manager)
                        <option value="{{ $manager['id'] }}">{{ $manager['name'] }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="vestra-companies__filter-panel-group">
            <label class="vestra-companies__filter-panel-legend" for="panel-date-range">Date Range</label>
            <select
                id="panel-date-range"
                class="vestra-companies__filter-panel-select"
                wire:change="setDatePreset($event.target.value)"
            >
                <option value="" @selected($datePreset === '')>All time</option>
                <option value="this_week" @selected($datePreset === 'this_week')>This Week</option>
                <option value="this_month" @selected($datePreset === 'this_month')>This Month</option>
                <option value="last_30" @selected($datePreset === 'last_30')>Last 30 days</option>
                <option value="last_90" @selected($datePreset === 'last_90')>Last 90 days</option>
            </select>
        </div>

        @if (! empty($regionOptions))
            <div class="vestra-companies__filter-panel-group">
                <label class="vestra-companies__filter-panel-legend" for="panel-region">Region</label>
                <select
                    id="panel-region"
                    class="vestra-companies__filter-panel-select"
                    wire:change="$set('regionFilter', $event.target.value ? [$event.target.value] : [])"
                >
                    <option value="" @selected($selectedRegion === '')>All regions</option>
                    @foreach ($regionOptions as $region)
                        <option value="{{ $region }}" @selected($selectedRegion === $region)>{{ $region }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if (! empty($districtOptions))
            <div class="vestra-companies__filter-panel-group">
                <label class="vestra-companies__filter-panel-legend" for="panel-district">District</label>
                <select
                    id="panel-district"
                    class="vestra-companies__filter-panel-select"
                    wire:change="$set('districtFilter', $event.target.value ? [$event.target.value] : [])"
                >
                    <option value="" @selected($selectedDistrict === '')>All districts</option>
                    @foreach ($districtOptions as $district)
                        <option value="{{ $district }}" @selected($selectedDistrict === $district)>{{ $district }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="vestra-companies__filter-panel-group">
            <span class="vestra-companies__filter-panel-legend">Relationships</span>
            <label class="vestra-companies__filter-option">
                <input type="checkbox" wire:model.live="hasOpenQuotes" class="vestra-companies__filter-checkbox" />
                <span class="vestra-companies__filter-option-label">Has open quotes</span>
            </label>
            <label class="vestra-companies__filter-option">
                <input type="checkbox" wire:model.live="hasActiveTickets" class="vestra-companies__filter-checkbox" />
                <span class="vestra-companies__filter-option-label">Has active tickets</span>
            </label>
            <label class="vestra-companies__filter-option">
                <input type="checkbox" wire:model.live="hasDistributor" class="vestra-companies__filter-checkbox" />
                <span class="vestra-companies__filter-option-label">Has distributor</span>
            </label>
        </div>
    </div>

    <div class="vestra-companies__filter-panel-footer">
        <button type="button" wire:click="applyFilters" class="vestra-button vestra-button--primary vestra-companies__filter-panel-apply">
            Apply Filters
        </button>
    </div>
</aside>
