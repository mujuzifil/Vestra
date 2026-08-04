@props([
    'staff' => null,
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
])

@php
$columns = [
    ['key' => 'name',    'label' => 'Name',       'sortable' => true,  'field' => 'name'],
    ['key' => 'email',   'label' => 'Email',      'sortable' => true,  'field' => 'email'],
    ['key' => 'roles',   'label' => 'Roles',      'sortable' => false],
    ['key' => 'status',  'label' => 'Status',     'sortable' => true,  'field' => 'status'],
    ['key' => 'login',   'label' => 'Last Login', 'sortable' => true,  'field' => 'last_login_at'],
    ['key' => 'created', 'label' => 'Created',    'sortable' => true,  'field' => 'created_at'],
    ['key' => 'actions', 'label' => '',           'sortable' => false],
];
@endphp

<div class="vestra-staff__table-wrap" role="region" aria-label="Staff members" tabindex="0">
    <table class="vestra-staff__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-staff__th vestra-staff__th--{{ $column['key'] }}">
                        @if ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-staff__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-staff__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-staff__sort-inactive" />
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
            @foreach ($staff as $member)
                <x-staff.staff-row :member="$member" />
            @endforeach
        </tbody>
    </table>
</div>
