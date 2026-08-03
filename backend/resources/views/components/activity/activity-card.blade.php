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

<div class="vestra-activity-card @if ($selected) vestra-activity-card--selected @endif">
    <div class="vestra-activity-card__select">
        <input
            type="checkbox"
            wire:click="toggleSelection('{{ $activity['id'] ?? '' }}')"
            :checked="@js($selected)"
            class="vestra-activity-card__checkbox"
            aria-label="Select activity"
        />
    </div>

    <div class="vestra-activity-card__timeline-marker">
        <span class="vestra-activity-card__timeline-dot"></span>
        <span class="vestra-activity-card__timeline-line"></span>
    </div>

    <div class="vestra-activity-card__icon-wrapper">
        <span class="vestra-activity-card__icon vestra-activity-card__icon--{{ $activity['color'] ?? 'gray' }}">
            <x-filament::icon :icon="$activity['icon'] ?? 'heroicon-o-bolt'" class="h-5 w-5" />
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
        </div>

        <p class="vestra-activity-card__description">{{ $activity['description'] ?? '' }}</p>

        <div class="vestra-activity-card__meta">
            @if ($subject)
                <span class="vestra-activity-card__meta-item vestra-activity-card__meta-item--module">
                    {{ $subject['type'] ?? '' }}
                </span>
            @else
                <span class="vestra-activity-card__meta-item vestra-activity-card__meta-item--module">
                    {{ $activity['module'] ?? '' }}
                </span>
            @endif

            @if ($user)
                <span class="vestra-activity-card__meta-item vestra-activity-card__meta-item--user">
                    @if (! empty($user['avatar']))
                        <img src="{{ $user['avatar'] }}" alt="" class="vestra-activity-card__avatar" />
                    @else
                        <span class="vestra-activity-card__avatar vestra-activity-card__avatar--initials">
                            {{ $user['initials'] ?? strtoupper(substr($user['name'] ?? '', 0, 1)) }}
                        </span>
                    @endif
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

    <div class="vestra-activity-card__actions">
        @if ($status)
            <span class="vestra-activity-card__status vestra-activity-card__status--{{ $status->color() }}">
                <x-filament::icon :icon="$status->icon()" class="h-3.5 w-3.5" />
                {{ $status->label() }}
            </span>
        @endif

        <button
            type="button"
            wire:click="openDetailPanel('{{ $activity['id'] ?? '' }}')"
            class="vestra-activity-card__action"
            aria-label="View activity details"
        >
            <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
            <span>View</span>
        </button>
    </div>
</div>
