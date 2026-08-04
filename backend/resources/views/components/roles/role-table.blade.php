@props(['roles' => null, 'sortField' => 'name', 'sortDirection' => 'asc'])

@php
$columns = [
    ['key' => 'name', 'label' => 'Role', 'sortable' => true, 'field' => 'name'],
    ['key' => 'description', 'label' => 'Description', 'sortable' => false],
    ['key' => 'type', 'label' => 'Type', 'sortable' => false],
    ['key' => 'users', 'label' => 'Users', 'sortable' => true, 'field' => 'users_count'],
    ['key' => 'permissions', 'label' => 'Permissions', 'sortable' => true, 'field' => 'permissions_count'],
    ['key' => 'created', 'label' => 'Created', 'sortable' => true, 'field' => 'created_at'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
$systemNames = \App\Services\Admin\RoleAdminService::SYSTEM_ROLE_NAMES;
@endphp

<div class="vestra-roles__table-wrap" role="region" aria-label="Roles" tabindex="0">
    <table class="vestra-roles__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-roles__th">
                        @if ($column['sortable'])
                            <button type="button" wire:click="sortBy('{{ $column['field'] }}')" class="vestra-roles__sort-btn">
                                <span>{{ $column['label'] }}</span>
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
                <x-roles.role-row :role="$role" :system-names="$systemNames" />
            @endforeach
        </tbody>
    </table>
</div>
