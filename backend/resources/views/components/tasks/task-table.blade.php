@props([
    'tasks' => null,
    'sortField' => 'due_date',
    'sortDirection' => 'asc',
])

@php
$columns = [
    ['key' => 'task', 'label' => 'Task', 'sortable' => false],
    ['key' => 'related', 'label' => 'Related To', 'sortable' => false],
    ['key' => 'assignee', 'label' => 'Assignee', 'sortable' => true, 'field' => 'assignee'],
    ['key' => 'priority', 'label' => 'Priority', 'sortable' => true, 'field' => 'priority'],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'field' => 'status'],
    ['key' => 'due_date', 'label' => 'Due Date', 'sortable' => true, 'field' => 'due_date'],
    ['key' => 'created', 'label' => 'Created', 'sortable' => true, 'field' => 'newest'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
@endphp

<div class="vestra-tasks__table-wrap" role="region" aria-label="Tasks" tabindex="0">
    <table class="vestra-tasks__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-tasks__th vestra-tasks__th--{{ $column['key'] }}">
                        @if ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-tasks__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-tasks__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-tasks__sort-inactive" />
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
            @foreach ($tasks as $task)
                <x-tasks.task-row :task="$task" />
            @endforeach
        </tbody>
    </table>
</div>
