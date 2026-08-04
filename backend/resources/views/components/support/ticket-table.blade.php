@props([
    'tickets' => null,
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
    'selectedIds' => [],
])

@php
$columns = [
    ['key' => 'select',           'label' => '',             'sortable' => false],
    ['key' => 'reference',        'label' => 'Reference',    'sortable' => true,  'field' => 'reference_number'],
    ['key' => 'subject',          'label' => 'Subject',      'sortable' => true,  'field' => 'subject'],
    ['key' => 'customer',         'label' => 'Customer',     'sortable' => false],
    ['key' => 'enquiry_type',     'label' => 'Type',         'sortable' => true,  'field' => 'enquiry_type'],
    ['key' => 'priority',         'label' => 'Priority',     'sortable' => true,  'field' => 'priority'],
    ['key' => 'status',           'label' => 'Status',       'sortable' => true,  'field' => 'status'],
    ['key' => 'assigned',         'label' => 'Assigned To',  'sortable' => true,  'field' => 'assigned_to'],
    ['key' => 'created_at',       'label' => 'Submitted',    'sortable' => true,  'field' => 'created_at'],
    ['key' => 'actions',          'label' => '',             'sortable' => false],
];
$pageIds = $tickets->pluck('id')->map(fn ($id) => (int) $id)->all();
$allSelected = count($pageIds) > 0 && count(array_intersect($selectedIds, $pageIds)) === count($pageIds);
@endphp

<div class="vestra-support__table-wrap" role="region" aria-label="Support tickets" tabindex="0">
    <table class="vestra-support__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-support__th vestra-support__th--{{ $column['key'] }}">
                        @if ($column['key'] === 'select')
                            <input
                                type="checkbox"
                                class="vestra-support__filter-checkbox"
                                wire:click="toggleSelectAll"
                                @checked($allSelected)
                                aria-label="Select all tickets on this page"
                            />
                        @elseif ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-support__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-support__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-support__sort-inactive" />
                                    @endif
                                </span>
                            </button>
                        @else
                            <span>{{ $column['label'] }}</span>
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($tickets as $ticket)
                <x-support.ticket-row :ticket="$ticket" :selected-ids="$selectedIds" />
            @endforeach
        </tbody>
    </table>
</div>
