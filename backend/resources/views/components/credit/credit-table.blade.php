@props([
    'accounts' => null,
    'sortField' => 'updated_at',
    'sortDirection' => 'desc',
])

@php
$columns = [
    ['key' => 'distributor', 'label' => 'Distributor', 'sortable' => true, 'field' => 'distributor'],
    ['key' => 'country', 'label' => 'Country', 'sortable' => false],
    ['key' => 'limit', 'label' => 'Credit Limit', 'sortable' => true, 'field' => 'limit'],
    ['key' => 'balance', 'label' => 'Outstanding Balance', 'sortable' => true, 'field' => 'balance'],
    ['key' => 'available', 'label' => 'Available Credit', 'sortable' => false],
    ['key' => 'utilization', 'label' => 'Utilization', 'sortable' => false],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'field' => 'status'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
@endphp

<div class="vestra-credit__table-wrap" role="region" aria-label="Credit accounts" tabindex="0">
    <table class="vestra-credit__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-credit__th vestra-credit__th--{{ $column['key'] }}">
                        @if ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-credit__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-credit__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-credit__sort-inactive" />
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
            @foreach ($accounts as $account)
                <x-credit.credit-row :account="$account" />
            @endforeach
        </tbody>
    </table>
</div>
