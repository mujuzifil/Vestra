@props([
    'stocks' => null,
    'sortField' => 'updated_at',
    'sortDirection' => 'desc',
])

@php
$columns = [
    ['key' => 'product', 'label' => 'Product', 'sortable' => true, 'field' => 'product'],
    ['key' => 'sku', 'label' => 'SKU', 'sortable' => true, 'field' => 'sku'],
    ['key' => 'warehouse', 'label' => 'Warehouse', 'sortable' => true, 'field' => 'warehouse'],
    ['key' => 'quantity', 'label' => 'Qty', 'sortable' => true, 'field' => 'quantity'],
    ['key' => 'available', 'label' => 'Available', 'sortable' => true, 'field' => 'available'],
    ['key' => 'reserved', 'label' => 'Reserved', 'sortable' => true, 'field' => 'reserved_quantity'],
    ['key' => 'value', 'label' => 'Value', 'sortable' => true, 'field' => 'value'],
    ['key' => 'status', 'label' => 'Status', 'sortable' => false],
    ['key' => 'updated_at', 'label' => 'Updated', 'sortable' => true, 'field' => 'updated_at'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
@endphp

<div class="vestra-inventory__table-wrap" role="region" aria-label="Inventory stock" tabindex="0">
    <table class="vestra-inventory__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-inventory__th vestra-inventory__th--{{ $column['key'] }}">
                        @if ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-inventory__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-inventory__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-inventory__sort-inactive" />
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
            @foreach ($stocks as $stock)
                <x-inventory.stock-row :stock="$stock" />
            @endforeach
        </tbody>
    </table>
</div>
