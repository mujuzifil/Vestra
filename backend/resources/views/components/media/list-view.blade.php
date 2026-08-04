@props([
    'items' => null,
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
    'selectedIds' => [],
])

@php
$columns = [
    ['key' => 'select', 'label' => '', 'sortable' => false],
    ['key' => 'file', 'label' => 'File', 'sortable' => true, 'field' => 'name'],
    ['key' => 'type', 'label' => 'Type', 'sortable' => true, 'field' => 'type'],
    ['key' => 'source', 'label' => 'Source', 'sortable' => true, 'field' => 'source'],
    ['key' => 'owner', 'label' => 'Owner', 'sortable' => false],
    ['key' => 'size', 'label' => 'Size', 'sortable' => true, 'field' => 'size'],
    ['key' => 'created', 'label' => 'Uploaded', 'sortable' => true, 'field' => 'created_at'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
$pageIds = $items->pluck('id')->all();
$allSelected = count($pageIds) > 0 && count(array_intersect($selectedIds, $pageIds)) === count($pageIds);
@endphp

<div class="vestra-media__table-wrap" role="region" aria-label="Media files" tabindex="0">
    <table class="vestra-media__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-media__th vestra-media__th--{{ $column['key'] }}">
                        @if ($column['key'] === 'select')
                            <input
                                type="checkbox"
                                class="vestra-media__filter-checkbox"
                                wire:click="toggleSelectAll"
                                @checked($allSelected)
                                aria-label="Select all files on this page"
                            />
                        @elseif ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-media__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-media__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-media__sort-inactive" />
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
            @foreach ($items as $item)
                <x-media.media-row :item="$item" :selected-ids="$selectedIds" />
            @endforeach
        </tbody>
    </table>
</div>
