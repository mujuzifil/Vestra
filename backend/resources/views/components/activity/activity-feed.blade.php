@props([
    'activities' => null,
    'selectedIds' => [],
])

@php
$columns = [
    ['key' => 'select', 'label' => '', 'sortable' => false],
    ['key' => 'activity', 'label' => 'Activity', 'sortable' => false],
    ['key' => 'module', 'label' => 'Module', 'sortable' => false],
    ['key' => 'user', 'label' => 'User', 'sortable' => false],
    ['key' => 'time', 'label' => 'Time', 'sortable' => false],
    ['key' => 'details', 'label' => 'Details', 'sortable' => false],
];
@endphp

<div class="vestra-activity__feed" role="region" aria-label="Activity timeline" tabindex="0">
    <div class="vestra-activity__feed-header">
        @foreach ($columns as $column)
            <div class="vestra-activity__feed-col vestra-activity__feed-col--{{ $column['key'] }}">
                <span>{{ $column['label'] }}</span>
            </div>
        @endforeach
    </div>

    <div class="vestra-activity__feed-body">
        <div class="vestra-activity__timeline">
            @foreach ($activities as $activity)
                <x-activity.activity-card
                    :activity="$activity"
                    :selected="in_array($activity['id'], $selectedIds, true)"
                />
            @endforeach
        </div>
    </div>
</div>
