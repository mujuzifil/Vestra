@props([
    'companies' => null,
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
    'selectedIds' => [],
])

@php
$columns = [
    ['key' => 'select', 'label' => '', 'sortable' => false],
    ['key' => 'company', 'label' => 'Company', 'sortable' => true, 'field' => 'company_name'],
    ['key' => 'contact', 'label' => 'Contact', 'sortable' => true, 'field' => 'primary_contact_name'],
    ['key' => 'industry', 'label' => 'Industry', 'sortable' => true, 'field' => 'industry'],
    ['key' => 'phone', 'label' => 'Phone', 'sortable' => true, 'field' => 'primary_contact_phone'],
    ['key' => 'country', 'label' => 'Country', 'sortable' => true, 'field' => 'country'],
    ['key' => 'region', 'label' => 'Region', 'sortable' => true, 'field' => 'region'],
    ['key' => 'district', 'label' => 'District', 'sortable' => true, 'field' => 'district'],
    ['key' => 'account_manager', 'label' => 'Account Manager', 'sortable' => true, 'field' => 'account_manager'],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'field' => 'status'],
    ['key' => 'quotes', 'label' => 'Open Quotes', 'sortable' => false],
    ['key' => 'tickets', 'label' => 'Active Tickets', 'sortable' => false],
    ['key' => 'created', 'label' => 'Created', 'sortable' => true, 'field' => 'created_at'],
    ['key' => 'activity', 'label' => 'Last Activity', 'sortable' => true, 'field' => 'updated_at'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
$pageIds = $companies->pluck('id')->map(fn ($id) => (int) $id)->all();
$allSelected = count($pageIds) > 0 && count(array_intersect($selectedIds, $pageIds)) === count($pageIds);
@endphp

<div class="vestra-companies__table-wrap" role="region" aria-label="Companies" tabindex="0">
    <table class="vestra-companies__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-companies__th vestra-companies__th--{{ $column['key'] }}">
                        @if ($column['key'] === 'select')
                            <input
                                type="checkbox"
                                class="vestra-companies__filter-checkbox"
                                wire:click="toggleSelectAll"
                                @checked($allSelected)
                                aria-label="Select all companies on this page"
                            />
                        @elseif ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-companies__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-companies__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-companies__sort-inactive" />
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
            @foreach ($companies as $company)
                <x-companies.company-row :company="$company" :selected-ids="$selectedIds" />
            @endforeach
        </tbody>
    </table>
</div>
