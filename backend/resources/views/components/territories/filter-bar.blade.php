@props([
    'countryOptions' => [],
    'districtOptions' => [],
    'distributorOptions' => [],
    'activeFilterCount' => 0,
    'showFilterPanel' => true,
    'countryFilter' => [],
    'districtFilter' => [],
    'distributorFilter' => null,
])

@php
$countryOptions = is_array($countryOptions) ? $countryOptions : [];
$districtOptions = is_array($districtOptions) ? $districtOptions : [];
$distributorOptions = is_array($distributorOptions) ? $distributorOptions : [];
$countryFilter = is_array($countryFilter) ? $countryFilter : [];
$districtFilter = is_array($districtFilter) ? $districtFilter : [];

$countryLabel = count($countryFilter) === 0
    ? 'All'
    : (count($countryFilter) === 1 ? $countryFilter[0] : count($countryFilter).' selected');

$districtLabel = count($districtFilter) === 0
    ? 'All'
    : (count($districtFilter) === 1 ? $districtFilter[0] : count($districtFilter).' selected');

$distributorLabel = 'All';
if (filled($distributorFilter)) {
    $distributorLabel = collect($distributorOptions)->firstWhere('id', (int) $distributorFilter)['name'] ?? 'Selected';
}
@endphp

<div class="vestra-territories__filter-bar">
    <div class="vestra-territories__filters">
        <div class="vestra-territories__toolbar-search">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="vestra-territories__toolbar-search-icon" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search branches, managers, cities..."
                class="vestra-territories__toolbar-search-input"
                aria-label="Search territories"
            />
        </div>

        @if (! empty($countryOptions))
            <div class="vestra-territories__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-territories__filter-trigger" aria-haspopup="listbox">
                    <span>Country: {{ $countryLabel }}</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-territories__filter-dropdown" role="listbox">
                    @foreach ($countryOptions as $country)
                        <label class="vestra-territories__filter-option">
                            <input type="checkbox" wire:model.live="countryFilter" value="{{ $country }}" class="vestra-territories__filter-checkbox" />
                            <span>{{ $country }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($districtOptions))
            <div class="vestra-territories__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-territories__filter-trigger" aria-haspopup="listbox">
                    <span>District: {{ $districtLabel }}</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-territories__filter-dropdown" role="listbox">
                    @foreach ($districtOptions as $district)
                        <label class="vestra-territories__filter-option">
                            <input type="checkbox" wire:model.live="districtFilter" value="{{ $district }}" class="vestra-territories__filter-checkbox" />
                            <span>{{ $district }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($distributorOptions))
            <div class="vestra-territories__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-territories__filter-trigger" aria-haspopup="listbox">
                    <span>Distributor: {{ $distributorLabel }}</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-territories__filter-dropdown vestra-territories__filter-dropdown--wide" role="listbox">
                    <label class="vestra-territories__filter-option">
                        <input type="radio" wire:model.live="distributorFilter" value="" class="vestra-territories__filter-radio" />
                        <span>All</span>
                    </label>
                    @foreach ($distributorOptions as $distributor)
                        <label class="vestra-territories__filter-option">
                            <input type="radio" wire:model.live="distributorFilter" value="{{ $distributor['id'] }}" class="vestra-territories__filter-radio" />
                            <span>{{ $distributor['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <button
        type="button"
        wire:click="toggleFilterPanel"
        class="vestra-territories__filters-toggle @if ($showFilterPanel) vestra-territories__filters-toggle--active @endif"
        aria-label="Toggle advanced filters"
        aria-pressed="{{ $showFilterPanel ? 'true' : 'false' }}"
    >
        <x-filament::icon icon="heroicon-o-funnel" class="h-4 w-4" />
        <span>Filters</span>
        @if ($activeFilterCount > 0)
            <span class="vestra-territories__filters-badge">{{ $activeFilterCount }}</span>
        @endif
    </button>
</div>
