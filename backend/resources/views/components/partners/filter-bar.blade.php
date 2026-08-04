@props([
    'statusOptions' => [],
    'countryOptions' => [],
    'regionOptions' => [],
    'salesRepOptions' => [],
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$countryOptions = is_array($countryOptions) ? $countryOptions : [];
$regionOptions = is_array($regionOptions) ? $regionOptions : [];
$salesRepOptions = is_array($salesRepOptions) ? $salesRepOptions : [];
@endphp

<div class="vestra-partners__filter-bar">
    <div class="vestra-partners__filters">
        <div class="vestra-partners__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-partners__filter-trigger" aria-haspopup="listbox">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-partners__filter-dropdown" role="listbox">
                @foreach ($statusOptions as $status)
                    <label class="vestra-partners__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $status['value'] }}"
                            class="vestra-partners__filter-checkbox"
                        />
                        <span class="vestra-partners__filter-option-label">{{ $status['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @if (! empty($countryOptions))
            <div class="vestra-partners__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-partners__filter-trigger" aria-haspopup="listbox">
                    <span>Country</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-partners__filter-dropdown" role="listbox">
                    @foreach ($countryOptions as $country)
                        <label class="vestra-partners__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="countryFilter"
                                value="{{ $country }}"
                                class="vestra-partners__filter-checkbox"
                            />
                            <span class="vestra-partners__filter-option-label">{{ $country }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($regionOptions))
            <div class="vestra-partners__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-partners__filter-trigger" aria-haspopup="listbox">
                    <span>Region</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-partners__filter-dropdown" role="listbox">
                    @foreach ($regionOptions as $region)
                        <label class="vestra-partners__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="regionFilter"
                                value="{{ $region }}"
                                class="vestra-partners__filter-checkbox"
                            />
                            <span class="vestra-partners__filter-option-label">{{ $region }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($salesRepOptions))
            <div class="vestra-partners__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-partners__filter-trigger" aria-haspopup="listbox">
                    <span>Sales Rep</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-partners__filter-dropdown vestra-partners__filter-dropdown--wide" role="listbox">
                    <label class="vestra-partners__filter-option">
                        <input
                            type="radio"
                            wire:model.live="salesRepFilter"
                            value=""
                            class="vestra-partners__filter-radio"
                        />
                        <span class="vestra-partners__filter-option-label">All sales reps</span>
                    </label>
                    @foreach ($salesRepOptions as $rep)
                        <label class="vestra-partners__filter-option">
                            <input
                                type="radio"
                                wire:model.live="salesRepFilter"
                                value="{{ $rep['id'] }}"
                                class="vestra-partners__filter-radio"
                            />
                            <span class="vestra-partners__filter-option-label">{{ $rep['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-partners__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
