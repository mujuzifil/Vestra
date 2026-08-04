@props([
    'products' => null,
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
    'selectedIds' => [],
])

@php
$columns = [
    ['key' => 'select',   'label' => '',         'sortable' => false],
    ['key' => 'product',  'label' => 'Product',  'sortable' => true,  'field' => 'name'],
    ['key' => 'sku',      'label' => 'SKU',      'sortable' => true,  'field' => 'sku'],
    ['key' => 'category', 'label' => 'Category', 'sortable' => true,  'field' => 'category'],
    ['key' => 'price',    'label' => 'Price',    'sortable' => true,  'field' => 'price'],
    ['key' => 'stock',    'label' => 'Stock',    'sortable' => true,  'field' => 'stock_quantity'],
    ['key' => 'status',   'label' => 'Status',   'sortable' => true,  'field' => 'status'],
    ['key' => 'featured', 'label' => 'Featured', 'sortable' => true,  'field' => 'featured'],
    ['key' => 'updated',  'label' => 'Updated',  'sortable' => true,  'field' => 'updated_at'],
    ['key' => 'actions',  'label' => '',         'sortable' => false],
];
$pageIds = $products->pluck('id')->map(fn ($id) => (int) $id)->all();
$allSelected = count($pageIds) > 0 && count(array_intersect($selectedIds, $pageIds)) === count($pageIds);
@endphp

<div class="vestra-products__table-wrap" role="region" aria-label="Products" tabindex="0">
    <table class="vestra-products__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-products__th vestra-products__th--{{ $column['key'] }}">
                        @if ($column['key'] === 'select')
                            <input
                                type="checkbox"
                                class="vestra-products__filter-checkbox"
                                wire:click="toggleSelectAll"
                                @checked($allSelected)
                                aria-label="Select all products on this page"
                            />
                        @elseif ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-products__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-products__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-products__sort-inactive" />
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
            @foreach ($products as $product)
                <x-products.product-row :product="$product" :selected-ids="$selectedIds" />
            @endforeach
        </tbody>
    </table>
</div>
