@props([
    'statusOptions' => [],
    'industryOptions' => [],
    'countryOptions' => [],
    'accountManagerOptions' => [],
    'activeFilterCount' => 0,
    'showFilterPanel' => true,
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$industryOptions = is_array($industryOptions) ? $industryOptions : [];
$countryOptions = is_array($countryOptions) ? $countryOptions : [];
$accountManagerOptions = is_array($accountManagerOptions) ? $accountManagerOptions : [];
@endphp

<div class="vestra-companies__filter-bar">
    <div class="vestra-companies__filters">
        <div class="vestra-companies__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-companies__filter-trigger" aria-haspopup="listbox">
                <span>Status</span>
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
                    <span>Industry</span>
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
                    <span>Country</span>
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
                    <span>Account Manager</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-companies__filter-dropdown vestra-companies__filter-dropdown--wide" role="listbox">
                    <label class="vestra-companies__filter-option">
                        <input type="radio" wire:model.live="accountManagerFilter" value="" class="vestra-companies__filter-radio" />
                        <span class="vestra-companies__filter-option-label">All account managers</span>
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

        <button
            type="button"
            wire:click="toggleFilterPanel"
            class="vestra-companies__filters-toggle"
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
