@props(['staff' => null, 'sortField' => 'created_at', 'sortDirection' => 'desc'])

@php
$columns = [
    ['key' => 'name', 'label' => 'Staff', 'sortable' => true, 'field' => 'name'],
    ['key' => 'email', 'label' => 'Email', 'sortable' => true, 'field' => 'email'],
    ['key' => 'roles', 'label' => 'Roles', 'sortable' => false],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'field' => 'status'],
    ['key' => 'last_login', 'label' => 'Last Login', 'sortable' => true, 'field' => 'last_login_at'],
    ['key' => 'joined', 'label' => 'Joined', 'sortable' => true, 'field' => 'created_at'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
@endphp

<div class="vestra-staff__table-wrap" role="region" aria-label="Staff members" tabindex="0">
    <table class="vestra-staff__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-staff__th">
                        @if ($column['sortable'])
                            <button type="button" wire:click="sortBy('{{ $column['field'] }}')" class="vestra-staff__sort-btn">
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
            @foreach ($staff as $member)
                <x-staff.staff-row :member="$member" />
            @endforeach
        </tbody>
    </table>
</div>
