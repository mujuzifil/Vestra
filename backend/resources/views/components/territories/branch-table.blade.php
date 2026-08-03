@props([
    'branches' => null,
    'sortField' => 'name',
    'sortDirection' => 'asc',
])

@php
$columns = [
    ['key' => 'branch', 'label' => 'Branch', 'sortable' => true, 'field' => 'name'],
    ['key' => 'distributor', 'label' => 'Distributor', 'sortable' => true, 'field' => 'distributor'],
    ['key' => 'manager', 'label' => 'Manager', 'sortable' => false],
    ['key' => 'location', 'label' => 'Location', 'sortable' => true, 'field' => 'country'],
    ['key' => 'coordinates', 'label' => 'Coordinates', 'sortable' => false],
    ['key' => 'service_areas', 'label' => 'Service Areas', 'sortable' => false],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'field' => 'status'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
@endphp

<div class="vestra-territories__table-wrap" role="region" aria-label="Territories" tabindex="0">
    <table class="vestra-territories__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-territories__th vestra-territories__th--{{ $column['key'] }}">
                        @if ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-territories__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-territories__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-territories__sort-inactive" />
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
            @foreach ($branches as $branch)
                <x-territories.branch-row :branch="$branch" />
            @endforeach
        </tbody>
    </table>
</div>
