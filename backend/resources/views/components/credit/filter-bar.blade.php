@props([
    'statusOptions' => [],
    'countryOptions' => [],
])

<div class="vestra-credit__filter-bar">
    <div class="vestra-credit__filters">
        <div class="vestra-credit__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-credit__filter-trigger" aria-haspopup="listbox">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-credit__filter-dropdown" role="listbox">
                @foreach ($statusOptions as $status)
                    <label class="vestra-credit__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $status }}"
                            class="vestra-credit__filter-checkbox"
                        />
                        <span class="vestra-credit__filter-option-label">{{ ucfirst($status) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @if (! empty($countryOptions))
            <div class="vestra-credit__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-credit__filter-trigger" aria-haspopup="listbox">
                    <span>Country</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-credit__filter-dropdown" role="listbox">
                    @foreach ($countryOptions as $country)
                        <label class="vestra-credit__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="countryFilter"
                                value="{{ $country }}"
                                class="vestra-credit__filter-checkbox"
                            />
                            <span class="vestra-credit__filter-option-label">{{ $country }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-credit__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
