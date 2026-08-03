@props([
    'show' => true,
    'statusOptions' => [],
    'countryOptions' => [],
    'districtOptions' => [],
    'distributorOptions' => [],
    'countryFilter' => [],
    'districtFilter' => [],
    'statusFilter' => [],
    'distributorFilter' => null,
    'activeFilterCount' => 0,
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$countryOptions = is_array($countryOptions) ? $countryOptions : [];
$districtOptions = is_array($districtOptions) ? $districtOptions : [];
$distributorOptions = is_array($distributorOptions) ? $distributorOptions : [];
$countryFilter = is_array($countryFilter) ? $countryFilter : [];
$districtFilter = is_array($districtFilter) ? $districtFilter : [];
$statusFilter = is_array($statusFilter) ? $statusFilter : [];

$selectedCountry = $countryFilter[0] ?? '';
$selectedDistrict = $districtFilter[0] ?? '';
$allStatusSelected = count($statusFilter) === 0;
@endphp

<aside
    class="vestra-territories__filter-panel @if ($show) vestra-territories__filter-panel--open @endif"
    aria-label="Territory filters"
>
    <div class="vestra-territories__filter-panel-header">
        <h2 class="vestra-territories__filter-panel-title">Filters</h2>
        <button type="button" wire:click="resetFilters" class="vestra-territories__filter-panel-clear">
            Clear all
        </button>
    </div>

    <div class="vestra-territories__filter-panel-body">
        <fieldset class="vestra-territories__filter-panel-group">
            <legend class="vestra-territories__filter-panel-legend">Status</legend>
            <label class="vestra-territories__filter-option">
                <input
                    type="checkbox"
                    class="vestra-territories__filter-checkbox"
                    wire:click.prevent="clearStatusFilter"
                    @checked($allStatusSelected)
                />
                <span>All</span>
            </label>
            @foreach ($statusOptions as $value => $label)
                <label class="vestra-territories__filter-option">
                    <input type="checkbox" wire:model.live="statusFilter" value="{{ $value }}" class="vestra-territories__filter-checkbox" />
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </fieldset>

        @if (! empty($countryOptions))
            <div class="vestra-territories__filter-panel-group">
                <label class="vestra-territories__filter-panel-legend" for="panel-country">Country</label>
                <select
                    id="panel-country"
                    class="vestra-territories__filter-panel-select"
                    wire:change="$set('countryFilter', $event.target.value ? [$event.target.value] : [])"
                >
                    <option value="" @selected($selectedCountry === '')>All countries</option>
                    @foreach ($countryOptions as $country)
                        <option value="{{ $country }}" @selected($selectedCountry === $country)>{{ $country }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if (! empty($districtOptions))
            <div class="vestra-territories__filter-panel-group">
                <label class="vestra-territories__filter-panel-legend" for="panel-district">District</label>
                <select
                    id="panel-district"
                    class="vestra-territories__filter-panel-select"
                    wire:change="$set('districtFilter', $event.target.value ? [$event.target.value] : [])"
                >
                    <option value="" @selected($selectedDistrict === '')>All districts</option>
                    @foreach ($districtOptions as $district)
                        <option value="{{ $district }}" @selected($selectedDistrict === $district)>{{ $district }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if (! empty($distributorOptions))
            <div class="vestra-territories__filter-panel-group">
                <label class="vestra-territories__filter-panel-legend" for="panel-distributor">Distributor</label>
                <select id="panel-distributor" wire:model.live="distributorFilter" class="vestra-territories__filter-panel-select">
                    <option value="">All distributors</option>
                    @foreach ($distributorOptions as $distributor)
                        <option value="{{ $distributor['id'] }}">{{ $distributor['name'] }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <div class="vestra-territories__filter-panel-footer">
        <button type="button" wire:click="applyFilters" class="vestra-button vestra-button--primary vestra-territories__filter-panel-apply">
            Apply Filters
        </button>
    </div>
</aside>
