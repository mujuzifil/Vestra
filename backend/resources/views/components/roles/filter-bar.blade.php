@props(['typeOptions' => []])

<div class="vestra-roles__filter-bar">
    <div class="vestra-roles__filters">
        <div class="vestra-roles__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-roles__filter-trigger">
                <span>Type</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" />
            </button>
            <div x-show="open" x-transition class="vestra-roles__filter-dropdown" role="listbox">
                <label class="vestra-roles__filter-option">
                    <input type="radio" wire:model.live="typeFilter" value="" class="vestra-roles__filter-radio" />
                    <span>All types</span>
                </label>
                @foreach ($typeOptions as $type)
                    <label class="vestra-roles__filter-option">
                        <input type="radio" wire:model.live="typeFilter" value="{{ $type['value'] }}" class="vestra-roles__filter-radio" />
                        <span>{{ $type['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
    <button type="button" wire:click="resetFilters" class="vestra-roles__reset-btn" aria-label="Reset filters">
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
