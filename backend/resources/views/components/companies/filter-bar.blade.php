@props([
    'statusOptions' => [],
    'industryOptions' => [],
    'countryOptions' => [],
    'accountManagerOptions' => [],
    'activeFilterCount' => 0,
    'showFilterPanel' => true,
    'statusFilter' => [],
    'industryFilter' => [],
    'countryFilter' => [],
    'accountManagerFilter' => null,
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$industryOptions = is_array($industryOptions) ? $industryOptions : [];
$countryOptions = is_array($countryOptions) ? $countryOptions : [];
$accountManagerOptions = is_array($accountManagerOptions) ? $accountManagerOptions : [];
$statusFilter = is_array($statusFilter) ? $statusFilter : [];
$industryFilter = is_array($industryFilter) ? $industryFilter : [];
$countryFilter = is_array($countryFilter) ? $countryFilter : [];

$statusLabel = count($statusFilter) === 0
    ? 'All'
    : (count($statusFilter) === 1
        ? (collect($statusOptions)->first(fn ($s) => $s->value === $statusFilter[0])?->label() ?? '1 selected')
        : count($statusFilter).' selected');

$industryLabel = count($industryFilter) === 0
    ? 'All'
    : (count($industryFilter) === 1 ? $industryFilter[0] : count($industryFilter).' selected');

$countryLabel = count($countryFilter) === 0
    ? 'All'
    : (count($countryFilter) === 1 ? $countryFilter[0] : count($countryFilter).' selected');

$managerLabel = 'All';
if (filled($accountManagerFilter)) {
    $managerLabel = collect($accountManagerOptions)->firstWhere('id', (int) $accountManagerFilter)['name'] ?? 'Selected';
}
@endphp

<div class="vestra-companies__filter-bar">
    <div class="vestra-companies__filters">
        <div class="vestra-companies__toolbar-search">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="vestra-companies__toolbar-search-icon" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search companies..."
                class="vestra-companies__toolbar-search-input"
                aria-label="Search companies"
            />
        </div>

        <div class="vestra-companies__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-companies__filter-trigger" aria-haspopup="listbox">
                <span>Status: {{ $statusLabel }}</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-companies__filter-dropdown" role="listbox">
                @foreach ($statusOptions as $status)
                    <label class="vestra-companies__filter-option">
                        <input type="checkbox" wire:model.live="statusFilter" value="{{ $status->value }}" class="vestra-companies__filter-checkbox" />
                        <span class="vestra-companies__filter-option-label">{{ $status->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @if (! empty($industryOptions))
            <div class="vestra-companies__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-companies__filter-trigger" aria-haspopup="listbox">
                    <span>Industry: {{ $industryLabel }}</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-companies__filter-dropdown" role="listbox">
                    @foreach ($industryOptions as $industry)
                        <label class="vestra-companies__filter-option">
                            <input type="checkbox" wire:model.live="industryFilter" value="{{ $industry }}" class="vestra-companies__filter-checkbox" />
                            <span class="vestra-companies__filter-option-label">{{ $industry }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($countryOptions))
            <div class="vestra-companies__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-companies__filter-trigger" aria-haspopup="listbox">
                    <span>Country: {{ $countryLabel }}</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-companies__filter-dropdown" role="listbox">
                    @foreach ($countryOptions as $country)
                        <label class="vestra-companies__filter-option">
                            <input type="checkbox" wire:model.live="countryFilter" value="{{ $country }}" class="vestra-companies__filter-checkbox" />
                            <span class="vestra-companies__filter-option-label">{{ $country }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($accountManagerOptions))
            <div class="vestra-companies__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-companies__filter-trigger" aria-haspopup="listbox">
                    <span>Account Manager: {{ $managerLabel }}</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-companies__filter-dropdown vestra-companies__filter-dropdown--wide" role="listbox">
                    <label class="vestra-companies__filter-option">
                        <input type="radio" wire:model.live="accountManagerFilter" value="" class="vestra-companies__filter-radio" />
                        <span class="vestra-companies__filter-option-label">All</span>
                    </label>
                    @foreach ($accountManagerOptions as $manager)
                        <label class="vestra-companies__filter-option">
                            <input type="radio" wire:model.live="accountManagerFilter" value="{{ $manager['id'] }}" class="vestra-companies__filter-radio" />
                            <span class="vestra-companies__filter-option-label">{{ $manager['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <button
        type="button"
        wire:click="toggleFilterPanel"
        class="vestra-companies__filters-toggle @if ($showFilterPanel) vestra-companies__filters-toggle--active @endif"
        aria-label="Toggle advanced filters"
        aria-pressed="{{ $showFilterPanel ? 'true' : 'false' }}"
    >
        <x-filament::icon icon="heroicon-o-funnel" class="h-4 w-4" />
        <span>Filters</span>
        @if ($activeFilterCount > 0)
            <span class="vestra-companies__filters-badge">{{ $activeFilterCount }}</span>
        @endif
    </button>
</div>
