@props([
    'typeOptions' => [],
])

@php
$typeOptions = is_array($typeOptions) ? $typeOptions : [];
@endphp

<div class="vestra-roles__filter-bar">
    <div class="vestra-roles__filters">
        <div class="vestra-roles__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-roles__filter-trigger" aria-haspopup="listbox">
                <span>Type</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-roles__filter-dropdown" role="listbox">
                @foreach ($typeOptions as $type)
                    <label class="vestra-roles__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="typeFilter"
                            value="{{ $type['value'] ?? $type }}"
                            class="vestra-roles__filter-checkbox"
                        />
                        <span class="vestra-roles__filter-option-label">{{ $type['label'] ?? ucfirst($type) }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-roles__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
