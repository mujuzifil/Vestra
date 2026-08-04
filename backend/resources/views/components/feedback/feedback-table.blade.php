@props([
    'feedback' => null,
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
])

@php
$columns = [
    ['key' => 'customer', 'label' => 'Customer', 'sortable' => false],
    ['key' => 'subject', 'label' => 'Subject', 'sortable' => true, 'field' => 'subject'],
    ['key' => 'category', 'label' => 'Category', 'sortable' => true, 'field' => 'category'],
    ['key' => 'priority', 'label' => 'Priority', 'sortable' => true, 'field' => 'priority'],
    ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'field' => 'status'],
    ['key' => 'read', 'label' => 'Read', 'sortable' => false],
    ['key' => 'submitted', 'label' => 'Submitted', 'sortable' => true, 'field' => 'created_at'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
@endphp

<div class="vestra-feedback__table-wrap" role="region" aria-label="Feedback list" tabindex="0">
    <table class="vestra-feedback__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-feedback__th vestra-feedback__th--{{ $column['key'] }}">
                        @if ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-feedback__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-feedback__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-feedback__sort-inactive" />
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
            @foreach ($feedback as $item)
                <x-feedback.feedback-row :feedback="$item" />
            @endforeach
        </tbody>
    </table>
</div>
