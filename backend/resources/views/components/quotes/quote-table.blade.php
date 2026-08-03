@props([
    'quotes' => null,
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
    'selectedIds' => [],
])

@php
$columns = [
    ['key' => 'select', 'label' => '', 'sortable' => false],
    ['key' => 'quote', 'label' => 'Quote #', 'sortable' => true, 'field' => 'reference_number'],
    ['key' => 'company', 'label' => 'Company', 'sortable' => true, 'field' => 'company_name'],
    ['key' => 'contact', 'label' => 'Contact', 'sortable' => false],
    ['key' => 'products', 'label' => 'Products', 'sortable' => false],
    ['key' => 'sales_rep', 'label' => 'Sales Rep', 'sortable' => true, 'field' => 'sales_rep'],
    ['key' => 'value', 'label' => 'Est. Value', 'sortable' => true, 'field' => 'estimated_value'],
    ['key' => 'priority', 'label' => 'Priority', 'sortable' => true, 'field' => 'priority'],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'field' => 'status'],
    ['key' => 'expiry', 'label' => 'Expiry', 'sortable' => true, 'field' => 'expected_close_date'],
    ['key' => 'created', 'label' => 'Created', 'sortable' => true, 'field' => 'created_at'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
$pageIds = $quotes->pluck('id')->map(fn ($id) => (int) $id)->all();
$allSelected = count($pageIds) > 0 && count(array_intersect($selectedIds, $pageIds)) === count($pageIds);
@endphp

<div class="vestra-quotes__table-wrap" role="region" aria-label="Quotes" tabindex="0">
    <table class="vestra-quotes__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-quotes__th vestra-quotes__th--{{ $column['key'] }}">
                        @if ($column['key'] === 'select')
                            <input
                                type="checkbox"
                                class="vestra-quotes__filter-checkbox"
                                wire:click="toggleSelectAll"
                                @checked($allSelected)
                                aria-label="Select all quotes on this page"
                            />
                        @elseif ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-quotes__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-quotes__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-quotes__sort-inactive" />
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
            @foreach ($quotes as $quote)
                <x-quotes.quote-row :quote="$quote" :selected-ids="$selectedIds" />
            @endforeach
        </tbody>
    </table>
</div>
