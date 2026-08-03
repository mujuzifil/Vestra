@props([
    'partners' => null,
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
])

@php
$columns = [
    ['key' => 'partner', 'label' => 'Partner', 'sortable' => true, 'field' => 'company_name'],
    ['key' => 'territory', 'label' => 'Territory', 'sortable' => false],
    ['key' => 'country', 'label' => 'Country', 'sortable' => true, 'field' => 'country'],
    ['key' => 'type', 'label' => 'Partner Type', 'sortable' => false],
    ['key' => 'rep', 'label' => 'Account Manager', 'sortable' => true, 'field' => 'sales_rep'],
    ['key' => 'credit-limit', 'label' => 'Credit Limit', 'sortable' => false],
    ['key' => 'utilization', 'label' => 'Credit Utilization', 'sortable' => false],
    ['key' => 'outstanding', 'label' => 'Outstanding', 'sortable' => false],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'field' => 'status'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
@endphp

<div class="vestra-partners__table-wrap" role="region" aria-label="Active partners" tabindex="0">
    <table class="vestra-partners__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-partners__th vestra-partners__th--{{ $column['key'] }}">
                        @if ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-partners__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-partners__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-partners__sort-inactive" />
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
            @foreach ($partners as $partner)
                <x-partners.partner-row :partner="$partner" />
            @endforeach
        </tbody>
    </table>
</div>
