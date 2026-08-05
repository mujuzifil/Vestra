@props([
    'items' => null,
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
])

@php
$columns = [
    ['key' => 'file', 'label' => 'File', 'sortable' => true, 'field' => 'file_name'],
    ['key' => 'type', 'label' => 'Type', 'sortable' => true, 'field' => 'media_type'],
    ['key' => 'size', 'label' => 'Size', 'sortable' => true, 'field' => 'size'],
    ['key' => 'usage', 'label' => 'Used In', 'sortable' => true, 'field' => 'usages'],
    ['key' => 'status', 'label' => 'Status', 'sortable' => false],
    ['key' => 'created', 'label' => 'Uploaded', 'sortable' => true, 'field' => 'created_at'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
@endphp

<div class="vestra-media__table-wrap" role="region" aria-label="Media assets" tabindex="0">
    <table class="vestra-media__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-media__th vestra-media__th--{{ $column['key'] }}">
                        @if ($column['sortable'])
                            <button type="button" wire:click="sortBy('{{ $column['field'] }}')" class="vestra-media__sort-btn">
                                <span>{{ $column['label'] }}</span>
                                @if ($sortField === $column['field'])
                                    <x-filament::icon icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}" class="h-3.5 w-3.5" />
                                @endif
                            </button>
                        @else
                            <span>{{ $column['label'] }}</span>
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <x-media.media-row :item="$item" />
            @endforeach
        </tbody>
    </table>
</div>
