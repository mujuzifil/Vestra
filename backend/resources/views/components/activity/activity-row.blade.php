@props([
    'activity' => [],
    'selected' => false,
])

@php
$activity = is_array($activity) ? $activity : [];
$user = $activity['user'] ?? null;
$category = $activity['category'] ?? null;
$status = $activity['status'] ?? null;
$activityId = $activity['id'] ?? '';
@endphp

<tr class="vestra-activity__row @if ($selected) vestra-activity__row--selected @endif" wire:key="activity-{{ $activityId }}">
    <td class="vestra-activity__td vestra-activity__td--select">
        <input
            type="checkbox"
            wire:click="toggleSelection('{{ $activityId }}')"
            @checked($selected)
            class="vestra-activity__filter-checkbox"
            aria-label="Select activity"
        />
    </td>

    <td class="vestra-activity__td vestra-activity__td--activity">
        <div class="vestra-activity__activity-cell">
            <span class="vestra-activity__icon vestra-activity__icon--{{ $activity['color'] ?? 'gray' }}">
                <x-filament::icon :icon="$activity['icon'] ?? 'heroicon-o-bolt'" class="h-4 w-4" />
            </span>
            <div class="vestra-activity__activity-text">
                <span class="vestra-activity__title">{{ $activity['title'] ?? '' }}</span>
                <div class="vestra-activity__badges">
                    @if ($category)
                        <span class="vestra-activity__badge vestra-activity__badge--{{ $category->color() }}">
                            {{ $category->label() }}
                        </span>
                    @endif
                    @if ($status)
                        <span class="vestra-activity__status vestra-activity__status--{{ $status->color() }}">
                            {{ $status->label() }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </td>

    <td class="vestra-activity__td vestra-activity__td--module">
        <span class="vestra-activity__cell-text">{{ $activity['module'] ?? ($activity['subject']['type'] ?? '—') }}</span>
    </td>

    <td class="vestra-activity__td vestra-activity__td--user">
        @if ($user)
            <div class="vestra-activity__user">
                <span class="vestra-activity__avatar">{{ $user['initials'] ?? strtoupper(substr($user['name'] ?? '', 0, 1)) }}</span>
                <span class="vestra-activity__user-name">{{ $user['name'] }}</span>
            </div>
        @else
            <div class="vestra-activity__user">
                <span class="vestra-activity__avatar">S</span>
                <span class="vestra-activity__user-name">System</span>
            </div>
        @endif
    </td>

    <td class="vestra-activity__td vestra-activity__td--time">
        <span class="vestra-activity__time">{{ $activity['diff_for_humans'] ?? '' }}</span>
        @if (! empty($activity['created_at']))
            <span class="vestra-activity__row-meta">{{ $activity['created_at']->format('M j, Y g:i A') }}</span>
        @endif
    </td>

    <td class="vestra-activity__td vestra-activity__td--details">
        <span class="vestra-activity__details">{{ $activity['description'] ?? '—' }}</span>
    </td>
</tr>
