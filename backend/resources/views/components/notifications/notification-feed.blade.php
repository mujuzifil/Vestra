@props([
    'notifications' => null,
    'selectedIds' => [],
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
])

@php
$columns = [
    ['key' => 'select', 'label' => '', 'sortable' => false],
    ['key' => 'notification', 'label' => 'Notification', 'sortable' => false],
    ['key' => 'category', 'label' => 'Category', 'sortable' => false],
    ['key' => 'priority', 'label' => 'Priority', 'sortable' => true, 'field' => 'priority'],
    ['key' => 'date', 'label' => 'Date', 'sortable' => true, 'field' => 'created_at'],
    ['key' => 'actions', 'label' => '', 'sortable' => false],
];
@endphp

<div class="vestra-notifications__feed" role="region" aria-label="Notifications" tabindex="0">
    <div class="vestra-notifications__feed-header">
        @foreach ($columns as $column)
            <div class="vestra-notifications__feed-col vestra-notifications__feed-col--{{ $column['key'] }}">
                @if ($column['sortable'])
                    <button
                        type="button"
                        wire:click="sortBy('{{ $column['field'] }}')"
                        class="vestra-notifications__sort-btn"
                        aria-label="Sort by {{ $column['label'] }}"
                    >
                        <span>{{ $column['label'] }}</span>
                        <span class="vestra-notifications__sort-icons">
                            @if ($sortField === $column['field'])
                                <x-filament::icon
                                    icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                    class="h-3.5 w-3.5"
                                />
                            @else
                                <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-notifications__sort-inactive" />
                            @endif
                        </span>
                    </button>
                @else
                    <span>{{ $column['label'] }}</span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="vestra-notifications__feed-body">
        @foreach ($notifications as $notification)
            <x-notifications.notification-card
                :notification="$notification"
                :selected="in_array($notification->id, $selectedIds, true)"
            />
        @endforeach
    </div>
</div>
