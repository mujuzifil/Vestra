<div class="vestra-categories__filter-bar">
    <div class="vestra-categories__filters">
        <div class="vestra-categories__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-categories__filter-trigger" aria-haspopup="listbox">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-categories__filter-dropdown" role="listbox">
                @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                    <label class="vestra-categories__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $value }}"
                            class="vestra-categories__filter-checkbox"
                        />
                        <span class="vestra-categories__filter-option-label">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-categories__filter vestra-categories__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-categories__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Created</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-categories__filter-dropdown vestra-categories__filter-dropdown--wide">
                <div class="vestra-categories__filter-date-fields">
                    <label class="vestra-categories__filter-field">
                        <span class="vestra-categories__filter-field-label">From</span>
                        <input type="date" wire:model.live="dateFrom" class="vestra-categories__filter-input" />
                    </label>
                    <label class="vestra-categories__filter-field">
                        <span class="vestra-categories__filter-field-label">Until</span>
                        <input type="date" wire:model.live="dateUntil" class="vestra-categories__filter-input" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-categories__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
