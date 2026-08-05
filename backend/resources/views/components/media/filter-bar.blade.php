@props([
    'typeOptions' => [],
    'usageOptions' => [],
    'formatOptions' => [],
    'uploaderOptions' => [],
    'dateFrom' => null,
    'dateUntil' => null,
])

<div class="vestra-media__filter-bar">
    <div class="vestra-media__filters">
        <div class="vestra-media__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-media__filter-trigger">
                <span>Type</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" />
            </button>
            <div x-show="open" x-transition class="vestra-media__filter-dropdown" role="listbox">
                @foreach ($typeOptions as $type)
                    <label class="vestra-media__filter-option">
                        <input type="checkbox" wire:model.live="typeFilter" value="{{ $type['value'] }}" class="vestra-media__filter-checkbox" />
                        <span class="vestra-media__filter-option-label">{{ $type['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-media__filter">
            <select wire:model.live="usageFilter" class="vestra-media__filter-input" aria-label="Usage filter">
                <option value="">Usage</option>
                @foreach ($usageOptions as $usage)
                    <option value="{{ $usage['value'] }}">{{ $usage['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="vestra-media__filter">
            <select wire:model.live="formatFilter" class="vestra-media__filter-input" aria-label="Format filter">
                <option value="">Format</option>
                @foreach ($formatOptions as $format)
                    <option value="{{ $format }}">{{ $format }}</option>
                @endforeach
            </select>
        </div>

        <div class="vestra-media__filter">
            <select wire:model.live="uploaderFilter" class="vestra-media__filter-input" aria-label="Uploader filter">
                <option value="">Uploader</option>
                @foreach ($uploaderOptions as $uploader)
                    <option value="{{ $uploader['id'] }}">{{ $uploader['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="vestra-media__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-media__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Date</span>
            </button>
            <div x-show="open" x-transition class="vestra-media__filter-dropdown vestra-media__filter-dropdown--wide">
                <div class="vestra-media__filter-date-fields">
                    <div class="vestra-media__filter-field">
                        <label class="vestra-media__filter-field-label" for="media-date-from">From</label>
                        <input type="date" id="media-date-from" wire:model.live="dateFrom" class="vestra-media__filter-input" />
                    </div>
                    <div class="vestra-media__filter-field">
                        <label class="vestra-media__filter-field-label" for="media-date-until">Until</label>
                        <input type="date" id="media-date-until" wire:model.live="dateUntil" class="vestra-media__filter-input" />
                    </div>
                </div>
            </div>
        </div>

        <div class="vestra-media__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-media__filter-trigger">
                <span>Sort</span>
            </button>
            <div x-show="open" x-transition class="vestra-media__filter-dropdown">
                <button type="button" wire:click="sortBy('created_at')" class="vestra-media__filter-option vestra-media__filter-option--button">Date Uploaded</button>
                <button type="button" wire:click="sortBy('file_name')" class="vestra-media__filter-option vestra-media__filter-option--button">File Name</button>
                <button type="button" wire:click="sortBy('size')" class="vestra-media__filter-option vestra-media__filter-option--button">File Size</button>
                <button type="button" wire:click="sortBy('usages')" class="vestra-media__filter-option vestra-media__filter-option--button">Used In</button>
            </div>
        </div>
    </div>

    <button type="button" wire:click="resetFilters" class="vestra-media__reset-btn" aria-label="Reset filters">
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
