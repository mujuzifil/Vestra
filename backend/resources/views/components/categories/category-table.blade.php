@props([
    'categories' => null,
    'sortField' => 'sort_order',
    'sortDirection' => 'asc',
])

@php
$columns = [
    ['key' => 'name', 'label' => 'Category', 'sortable' => true, 'field' => 'name'],
    ['key' => 'slug', 'label' => 'Slug', 'sortable' => true, 'field' => 'slug'],
    ['key' => 'products', 'label' => 'Products', 'sortable' => true, 'field' => 'products_count'],
    ['key' => 'sort', 'label' => 'Sort', 'sortable' => true, 'field' => 'sort_order'],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'field' => 'status'],
    ['key' => 'updated', 'label' => 'Updated', 'sortable' => true, 'field' => 'updated_at'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
@endphp

<div class="vestra-categories__table-wrap" role="region" aria-label="Categories list" tabindex="0">
    <table class="vestra-categories__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-categories__th vestra-categories__th--{{ $column['key'] }}">
                        @if ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-categories__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-categories__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-categories__sort-inactive" />
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
            @foreach ($categories as $category)
                <x-categories.category-row :category="$category" />
            @endforeach
        </tbody>
    </table>
</div>
