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
$pageIds = collect($activities)->pluck('id')->map(fn ($id) => (string) $id)->all();
$allSelected = count($pageIds) > 0 && count(array_intersect($selectedIds, $pageIds)) === count($pageIds);
@endphp

<div class="vestra-activity__table-wrap" role="region" aria-label="Activity log" tabindex="0">
    <table class="vestra-activity__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-activity__th vestra-activity__th--{{ $column['key'] }}">
                        @if ($column['key'] === 'select')
                            <input
                                type="checkbox"
                                class="vestra-activity__filter-checkbox"
                                wire:click="selectPage({{ $allSelected ? 'false' : 'true' }})"
                                @checked($allSelected)
                                aria-label="Select all activities on this page"
                            />
                        @else
                            <span>{{ $column['label'] }}</span>
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($activities as $activity)
                <x-activity.activity-row
                    :activity="$activity"
                    :selected="in_array($activity['id'], $selectedIds, true)"
                />
            @endforeach
        </tbody>
    </table>
</div>
