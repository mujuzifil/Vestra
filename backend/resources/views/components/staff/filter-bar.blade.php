@props([
    'statusOptions' => [],
    'roleOptions' => [],
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$roleOptions = is_array($roleOptions) ? $roleOptions : [];
@endphp

<div class="vestra-staff__filter-bar">
    <div class="vestra-staff__filters">
        <div class="vestra-staff__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-staff__filter-trigger" aria-haspopup="listbox">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-staff__filter-dropdown" role="listbox">
                @foreach ($statusOptions as $status)
                    <label class="vestra-staff__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $status['value'] ?? $status }}"
                            class="vestra-staff__filter-checkbox"
                        />
                        <span class="vestra-staff__filter-option-label">{{ $status['label'] ?? ucfirst(str_replace('_', ' ', $status)) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @if (! empty($roleOptions))
            <div class="vestra-staff__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-staff__filter-trigger" aria-haspopup="listbox">
                    <span>Role</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-staff__filter-dropdown vestra-staff__filter-dropdown--wide" role="listbox">
                    @foreach ($roleOptions as $role)
                        <label class="vestra-staff__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="roleFilter"
                                value="{{ $role['id'] }}"
                                class="vestra-staff__filter-checkbox"
                            />
                            <span class="vestra-staff__filter-option-label">{{ $role['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-staff__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
