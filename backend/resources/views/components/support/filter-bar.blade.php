@props([
    'statusOptions' => [],
    'priorityOptions' => [],
    'enquiryTypeOptions' => [],
    'assigneeOptions' => [],
])

@php
$statusOptions = is_array($statusOptions) ? $statusOptions : [];
$priorityOptions = is_array($priorityOptions) ? $priorityOptions : [];
$enquiryTypeOptions = is_array($enquiryTypeOptions) ? $enquiryTypeOptions : [];
$assigneeOptions = is_array($assigneeOptions) ? $assigneeOptions : [];

$statusLabels = [
    'open'        => 'Open',
    'in_progress' => 'In Progress',
    'resolved'    => 'Resolved',
    'closed'      => 'Closed',
];
$priorityLabels = [
    'low'    => 'Low',
    'medium' => 'Medium',
    'high'   => 'High',
    'urgent' => 'Urgent',
];
@endphp

<div class="vestra-support__filter-bar">
    <div class="vestra-support__filters">
        <div class="vestra-support__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-support__filter-trigger" aria-haspopup="listbox">
                <span>Status</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-support__filter-dropdown" role="listbox">
                @foreach ($statusOptions as $status)
                    <label class="vestra-support__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="statusFilter"
                            value="{{ $status }}"
                            class="vestra-support__filter-checkbox"
                        />
                        <span class="vestra-support__filter-option-label">{{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="vestra-support__filter" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-support__filter-trigger" aria-haspopup="listbox">
                <span>Priority</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-support__filter-dropdown" role="listbox">
                @foreach ($priorityOptions as $priority)
                    <label class="vestra-support__filter-option">
                        <input
                            type="checkbox"
                            wire:model.live="priorityFilter"
                            value="{{ $priority }}"
                            class="vestra-support__filter-checkbox"
                        />
                        <span class="vestra-support__filter-option-label">{{ $priorityLabels[$priority] ?? ucfirst($priority) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @if (! empty($enquiryTypeOptions))
            <div class="vestra-support__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-support__filter-trigger" aria-haspopup="listbox">
                    <span>Enquiry Type</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-support__filter-dropdown" role="listbox">
                    @foreach ($enquiryTypeOptions as $type)
                        <label class="vestra-support__filter-option">
                            <input
                                type="checkbox"
                                wire:model.live="enquiryTypeFilter"
                                value="{{ $type }}"
                                class="vestra-support__filter-checkbox"
                            />
                            <span class="vestra-support__filter-option-label">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($assigneeOptions))
            <div class="vestra-support__filter" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="vestra-support__filter-trigger" aria-haspopup="listbox">
                    <span>Assigned To</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
                </button>
                <div x-show="open" x-transition class="vestra-support__filter-dropdown vestra-support__filter-dropdown--wide" role="listbox">
                    <label class="vestra-support__filter-option">
                        <input
                            type="radio"
                            wire:model.live="assignedToFilter"
                            value=""
                            class="vestra-support__filter-radio"
                        />
                        <span class="vestra-support__filter-option-label">All staff</span>
                    </label>
                    @foreach ($assigneeOptions as $assignee)
                        <label class="vestra-support__filter-option">
                            <input
                                type="radio"
                                wire:model.live="assignedToFilter"
                                value="{{ $assignee['id'] }}"
                                class="vestra-support__filter-radio"
                            />
                            <span class="vestra-support__filter-option-label">{{ $assignee['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="vestra-support__filter vestra-support__filter--date" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-support__filter-trigger">
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span>Submitted</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>
            <div x-show="open" x-transition class="vestra-support__filter-dropdown vestra-support__filter-dropdown--wide">
                <div class="vestra-support__filter-date-fields">
                    <label class="vestra-support__filter-field">
                        <span class="vestra-support__filter-field-label">From</span>
                        <input type="date" wire:model.live="dateFrom" class="vestra-support__filter-input" />
                    </label>
                    <label class="vestra-support__filter-field">
                        <span class="vestra-support__filter-field-label">Until</span>
                        <input type="date" wire:model.live="dateUntil" class="vestra-support__filter-input" />
                    </label>
                </div>
            </div>
        </div>
    </div>

    <button
        type="button"
        wire:click="resetFilters"
        class="vestra-support__reset-btn"
        aria-label="Reset filters"
    >
        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
        <span>Reset</span>
    </button>
</div>
