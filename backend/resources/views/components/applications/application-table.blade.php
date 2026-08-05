@props([
    'applications' => null,
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
    'selectedIds' => [],
])

@php
$columns = [
    ['key' => 'select', 'label' => '', 'sortable' => false],
    ['key' => 'company', 'label' => 'Company', 'sortable' => true, 'field' => 'company_name'],
    ['key' => 'contact', 'label' => 'Contact', 'sortable' => false],
    ['key' => 'location', 'label' => 'Location', 'sortable' => true, 'field' => 'country'],
    ['key' => 'priority', 'label' => 'Priority', 'sortable' => true, 'field' => 'priority'],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'field' => 'status'],
    ['key' => 'submitted', 'label' => 'Submitted', 'sortable' => true, 'field' => 'created_at'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
$pageIds = $applications->pluck('id')->map(fn ($id) => (int) $id)->all();
$allSelected = count($pageIds) > 0 && count(array_intersect($selectedIds, $pageIds)) === count($pageIds);
@endphp

<div class="vestra-applications__table-wrap" role="region" aria-label="Applications" tabindex="0">
    <table class="vestra-applications__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-applications__th vestra-applications__th--{{ $column['key'] }}">
                        @if ($column['key'] === 'select')
                            <input
                                type="checkbox"
                                class="vestra-applications__filter-checkbox"
                                wire:click="toggleSelectAll"
                                @checked($allSelected)
                                aria-label="Select all applications on this page"
                            />
                        @elseif ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-applications__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-applications__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-applications__sort-inactive" />
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
            @foreach ($applications as $application)
                <x-applications.application-row :application="$application" :selected-ids="$selectedIds" />
            @endforeach
        </tbody>
    </table>
</div>
