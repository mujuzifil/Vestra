@props([
    'statusOptions'     => [],
    'enquiryTypeOptions' => [],
    'priorityOptions'   => [],
    'sourceOptions'     => [],
    'assigneeOptions'   => [],
])

@php
$statusOptions      = is_array($statusOptions) ? $statusOptions : [];
$enquiryTypeOptions = is_array($enquiryTypeOptions) ? $enquiryTypeOptions : [];
$priorityOptions    = is_array($priorityOptions) ? $priorityOptions : [];
$sourceOptions      = is_array($sourceOptions) ? $sourceOptions : [];
$assigneeOptions    = is_array($assigneeOptions) ? $assigneeOptions : [];
@endphp

<div class="vestra-enquiries__filter-bar">
    <div class="vestra-enquiries__filters">

        {{-- Status --}}
        <div class="vestra-enquiries__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-enquiries__filter-trigger" aria-haspopup="listbox">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-enquiries__filter-dropdown" role="listbox">
                @foreach ($statusOptions as $status)
                    <label class="vestra-enquiries__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $status->value }}"
                            class="vestra-enquiries__filter-checkbox"
                        />
                        <span class="vestra-enquiries__filter-option-label">{{ $status->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Enquiry Type --}}
        <div class="vestra-enquiries__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-enquiries__filter-trigger" aria-haspopup="listbox">
                <span>Type</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-enquiries__filter-dropdown" role="listbox">
                @foreach ($enquiryTypeOptions as $type)
                    <label class="vestra-enquiries__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="enquiryTypeFilter"
                            value="{{ $type->value }}"
                            class="vestra-enquiries__filter-checkbox"
                        />
                        <span class="vestra-enquiries__filter-option-label">{{ $type->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Priority --}}
        <div class="vestra-enquiries__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-enquiries__filter-trigger" aria-haspopup="listbox">
                <span>Priority</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-enquiries__filter-dropdown" role="listbox">
                @foreach ($priorityOptions as $priority)
                    <label class="vestra-enquiries__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="priorityFilter"
                            value="{{ $priority->value }}"
                            class="vestra-enquiries__filter-checkbox"
                        />
                        <span class="vestra-enquiries__filter-option-label">{{ $priority->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Source --}}
        @if (! empty($sourceOptions))
            <div class="vestra-enquiries__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-enquiries__filter-trigger" aria-haspopup="listbox">
                    <span>Source</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-enquiries__filter-dropdown" role="listbox">
                    @foreach ($sourceOptions as $source)
                        <label class="vestra-enquiries__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="sourceFilter"
                                value="{{ $source }}"
                                class="vestra-enquiries__filter-checkbox"
                            />
                            <span class="vestra-enquiries__filter-option-label">{{ ucfirst($source) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Assigned To --}}
        @if (! empty($assigneeOptions))
            <div class="vestra-enquiries__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-enquiries__filter-trigger" aria-haspopup="listbox">
                    <span>Assigned To</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-enquiries__filter-dropdown vestra-enquiries__filter-dropdown--wide" role="listbox">
                    <label class="vestra-enquiries__filter-option">
                        <input
                            type="radio"
                            wire:model.live="assignedToFilter"
                            value=""
                            class="vestra-enquiries__filter-radio"
                        />
                        <span class="vestra-enquiries__filter-option-label">All administrators</span>
                    </label>
                    @foreach ($assigneeOptions as $assignee)
                        <label class="vestra-enquiries__filter-option">
                            <input
                                type="radio"
                                wire:model.live="assignedToFilter"
                                value="{{ $assignee['id'] }}"
                                class="vestra-enquiries__filter-radio"
                            />
                            <span class="vestra-enquiries__filter-option-label">{{ $assignee['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Date range --}}
        <div class="vestra-enquiries__filter vestra-enquiries__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-enquiries__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Received</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-enquiries__filter-dropdown vestra-enquiries__filter-dropdown--wide">
                <div class="vestra-enquiries__filter-date-fields">
                    <label class="vestra-enquiries__filter-field">
                        <span class="vestra-enquiries__filter-field-label">From</span>
                        <input type="date" wire:model.live="dateFrom" class="vestra-enquiries__filter-input" />
                    </label>
                    <label class="vestra-enquiries__filter-field">
                        <span class="vestra-enquiries__filter-field-label">Until</span>
                        <input type="date" wire:model.live="dateUntil" class="vestra-enquiries__filter-input" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-enquiries__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
