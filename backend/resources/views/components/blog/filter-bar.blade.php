@props([
    'statusOptions' => [],
    'authorOptions' => [],
    'categoryOptions' => [],
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$authorOptions = is_array($authorOptions) ? $authorOptions : [];
$categoryOptions = is_array($categoryOptions) ? $categoryOptions : [];
@endphp

<div class="vestra-blog__filter-bar">
    <div class="vestra-blog__filters">
        <div class="vestra-blog__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-blog__filter-trigger" aria-haspopup="listbox">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-blog__filter-dropdown" role="listbox">
                @foreach ($statusOptions as $status)
                    <label class="vestra-blog__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $status['value'] ?? $status }}"
                            class="vestra-blog__filter-checkbox"
                        />
                        <span class="vestra-blog__filter-option-label">{{ $status['label'] ?? ucfirst(str_replace('_', ' ', $status)) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @if (! empty($authorOptions))
            <div class="vestra-blog__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-blog__filter-trigger" aria-haspopup="listbox">
                    <span>Author</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-blog__filter-dropdown vestra-blog__filter-dropdown--wide" role="listbox">
                    <label class="vestra-blog__filter-option">
                        <input
                            type="radio"
                            wire:model.live="authorFilter"
                            value=""
                            class="vestra-blog__filter-radio"
                        />
                        <span class="vestra-blog__filter-option-label">All authors</span>
                    </label>
                    @foreach ($authorOptions as $author)
                        <label class="vestra-blog__filter-option">
                            <input
                                type="radio"
                                wire:model.live="authorFilter"
                                value="{{ $author['id'] }}"
                                class="vestra-blog__filter-radio"
                            />
                            <span class="vestra-blog__filter-option-label">{{ $author['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($categoryOptions))
            <div class="vestra-blog__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-blog__filter-trigger" aria-haspopup="listbox">
                    <span>Category</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-blog__filter-dropdown vestra-blog__filter-dropdown--wide" role="listbox">
                    @foreach ($categoryOptions as $category)
                        <label class="vestra-blog__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="categoryFilter"
                                value="{{ $category['id'] }}"
                                class="vestra-blog__filter-checkbox"
                            />
                            <span class="vestra-blog__filter-option-label">{{ $category['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="vestra-blog__filter vestra-blog__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-blog__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Created</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-blog__filter-dropdown vestra-blog__filter-dropdown--wide">
                <div class="vestra-blog__filter-date-fields">
                    <label class="vestra-blog__filter-field">
                        <span class="vestra-blog__filter-field-label">From</span>
                        <input type="date" wire:model.live="dateFrom" class="vestra-blog__filter-input" />
                    </label>
                    <label class="vestra-blog__filter-field">
                        <span class="vestra-blog__filter-field-label">Until</span>
                        <input type="date" wire:model.live="dateUntil" class="vestra-blog__filter-input" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-blog__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
