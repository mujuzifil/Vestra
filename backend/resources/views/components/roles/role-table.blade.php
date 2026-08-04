@props([
    'roles' => null,
    'sortField' => 'name',
    'sortDirection' => 'asc',
])

@php
$columns = [
    ['key' => 'name',        'label' => 'Role',        'sortable' => true,  'field' => 'name'],
    ['key' => 'type',        'label' => 'Type',        'sortable' => false],
    ['key' => 'description', 'label' => 'Description', 'sortable' => false],
    ['key' => 'users',       'label' => 'Users',        'sortable' => true,  'field' => 'users_count'],
    ['key' => 'permissions', 'label' => 'Permissions',  'sortable' => true,  'field' => 'permissions_count'],
    ['key' => 'updated',     'label' => 'Updated',      'sortable' => true,  'field' => 'updated_at'],
    ['key' => 'actions',     'label' => '',             'sortable' => false],
];
@endphp

<div class="vestra-roles__table-wrap" role="region" aria-label="Roles" tabindex="0">
    <table class="vestra-roles__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-roles__th vestra-roles__th--{{ $column['key'] }}">
                        @if ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-roles__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-roles__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-roles__sort-inactive" />
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
            @foreach ($roles as $role)
                <x-roles.role-row :role="$role" />
            @endforeach
        </tbody>
    </table>
</div>
