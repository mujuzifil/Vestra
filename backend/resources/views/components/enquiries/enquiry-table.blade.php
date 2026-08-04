@props([
    'enquiries'     => null,
    'sortField'     => 'created_at',
    'sortDirection' => 'desc',
])

@php
$columns = [
    ['key' => 'sender',       'label' => 'Sender',       'sortable' => true,  'field' => 'name'],
    ['key' => 'subject',      'label' => 'Subject',      'sortable' => false],
    ['key' => 'enquiry_type', 'label' => 'Type',         'sortable' => true,  'field' => 'enquiry_type'],
    ['key' => 'priority',     'label' => 'Priority',     'sortable' => true,  'field' => 'priority'],
    ['key' => 'status',       'label' => 'Status',       'sortable' => true,  'field' => 'status'],
    ['key' => 'assigned',     'label' => 'Assigned To',  'sortable' => true,  'field' => 'assigned_to'],
    ['key' => 'read',         'label' => 'Read',         'sortable' => false],
    ['key' => 'replied',      'label' => 'Replied',      'sortable' => false],
    ['key' => 'received',     'label' => 'Received',     'sortable' => true,  'field' => 'created_at'],
    ['key' => 'actions',      'label' => '',             'sortable' => false],
];
@endphp

<div class="vestra-enquiries__table-wrap" role="region" aria-label="Enquiries" tabindex="0">
    <table class="vestra-enquiries__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-enquiries__th vestra-enquiries__th--{{ $column['key'] }}">
                        @if ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-enquiries__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-enquiries__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-enquiries__sort-inactive" />
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
            @foreach ($enquiries as $enquiry)
                <x-enquiries.enquiry-row :enquiry="$enquiry" :sort-field="$sortField" />
            @endforeach
        </tbody>
    </table>
</div>
