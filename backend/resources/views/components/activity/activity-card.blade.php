@props([
    'activity' => [],
    'selected' => false,
])

@php
$activity = is_array($activity) ? $activity : [];
$user = $activity['user'] ?? null;
$subject = $activity['subject'] ?? null;
$category = $activity['category'] ?? null;
$status = $activity['status'] ?? null;
@endphp

<article class="vestra-activity-card @if ($selected) vestra-activity-card--selected @endif">
    <div class="vestra-activity-card__select">
        <input
            type="checkbox"
            wire:click="toggleSelection('{{ $activity['id'] ?? '' }}')"
            :checked="@js($selected)"
            class="vestra-activity-card__checkbox"
            aria-label="Select activity"
        />
    </div>

    <div class="vestra-activity-card__icon-wrapper">
        <span class="vestra-activity-card__icon vestra-activity-card__icon--{{ $activity['color'] ?? 'gray' }}">
            <x-filament::icon :icon="$activity['icon'] ?? 'heroicon-o-bolt'" class="h-4 w-4" />
        </span>
    </div>

    <div class="vestra-activity-card__content">
        <div class="vestra-activity-card__row vestra-activity-card__row--primary">
            <h4 class="vestra-activity-card__title">{{ $activity['title'] ?? '' }}</h4>
            @if ($category)
                <span class="vestra-activity-card__badge vestra-activity-card__badge--{{ $category->color() }}">
                    {{ $category->label() }}
                </span>
            @endif
            @if ($status)
                <span class="vestra-activity-card__status vestra-activity-card__status--{{ $status->color() }}">
                    {{ $status->label() }}
                </span>
            @endif
        </div>

        @if (! empty($activity['description']))
            <p class="vestra-activity-card__description">{{ $activity['description'] }}</p>
        @endif

        <div class="vestra-activity-card__meta">
            <span class="vestra-activity-card__meta-item vestra-activity-card__meta-item--module">
                {{ $subject['type'] ?? ($activity['module'] ?? '') }}
            </span>

            @if ($user)
                <span class="vestra-activity-card__meta-item vestra-activity-card__meta-item--user">
                    <span class="vestra-activity-card__avatar vestra-activity-card__avatar--initials">
                        {{ $user['initials'] ?? strtoupper(substr($user['name'] ?? '', 0, 1)) }}
                    </span>
                    {{ $user['name'] }}
                </span>
            @else
                <span class="vestra-activity-card__meta-item vestra-activity-card__meta-item--user">
                    <span class="vestra-activity-card__avatar vestra-activity-card__avatar--initials">S</span>
                    System
                </span>
            @endif

            <span class="vestra-activity-card__meta-item vestra-activity-card__meta-item--time">
                <x-filament::icon icon="heroicon-o-clock" class="h-3.5 w-3.5" />
                {{ $activity['diff_for_humans'] ?? '' }}
            </span>
        </div>
    </div>
</article>
