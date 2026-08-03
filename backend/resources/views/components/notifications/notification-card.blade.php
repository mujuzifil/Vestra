@props([
    'notification' => null,
    'selected' => false,
])

@php
use App\Enums\NotificationCategory;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;

$data = $notification->data ?? [];
$type = NotificationType::tryFromString($data['type'] ?? null) ?? NotificationType::SYSTEM;
$category = NotificationCategory::tryFromString($data['category'] ?? null) ?? $type->category();
$priority = NotificationPriority::tryFromString($data['priority'] ?? null) ?? NotificationPriority::INFORMATION;
$title = $data['title'] ?? $type->label();
$message = $data['message'] ?? '';
$icon = $type->icon();
$isUnread = $notification->unread();
$timeAgo = $notification->created_at?->diffForHumans() ?? '';
@endphp

<div
    class="vestra-notification-card @if ($isUnread) vestra-notification-card--unread @endif"
    role="button"
    tabindex="0"
    wire:click="openDetailPanel('{{ $notification->id }}')"
    aria-label="{{ $isUnread ? 'Unread' : 'Read' }} notification: {{ $title }}"
>
    <div class="vestra-notification-card__select" onclick="event.stopPropagation();">
        <input
            type="checkbox"
            wire:click="toggleSelection('{{ $notification->id }}')"
            :checked="{{ $selected ? 'true' : 'false' }}"
            class="vestra-notification-card__checkbox"
            aria-label="Select notification"
        />
    </div>

    <div class="vestra-notification-card__icon-wrapper">
        <span class="vestra-notification-card__icon vestra-notification-card__icon--{{ $priority->color() }}">
            <x-filament::icon :icon="$icon" class="h-5 w-5" />
        </span>
    </div>

    <div class="vestra-notification-card__content">
        <div class="vestra-notification-card__row">
            <h3 class="vestra-notification-card__title">{{ $title }}</h3>
            @if ($isUnread)
                <span class="vestra-notification-card__unread-dot" aria-hidden="true"></span>
            @endif
        </div>
        @if ($message)
            <p class="vestra-notification-card__message">{{ Str::limit($message, 120) }}</p>
        @endif
    </div>

    <div class="vestra-notification-card__meta">
        <x-notifications.badge :value="$category->label()" :color="$category->color()" />
    </div>

    <div class="vestra-notification-card__priority">
        <x-notifications.badge :value="$priority->label()" :color="$priority->color()" />
    </div>

    <div class="vestra-notification-card__time">
        <x-filament::icon icon="heroicon-o-clock" class="h-3.5 w-3.5" />
        <span>{{ $timeAgo }}</span>
    </div>

    <div class="vestra-notification-card__actions" onclick="event.stopPropagation();">
        @if ($isUnread)
            <button
                type="button"
                wire:click="markAsRead('{{ $notification->id }}')"
                class="vestra-notification-card__action"
                aria-label="Mark as read"
                title="Mark as read"
            >
                <x-filament::icon icon="heroicon-o-envelope-open" class="h-4 w-4" />
            </button>
        @else
            <button
                type="button"
                wire:click="markAsUnread('{{ $notification->id }}')"
                class="vestra-notification-card__action"
                aria-label="Mark as unread"
                title="Mark as unread"
            >
                <x-filament::icon icon="heroicon-o-envelope" class="h-4 w-4" />
            </button>
        @endif

        <button
            type="button"
            wire:click="deleteNotification('{{ $notification->id }}')"
            class="vestra-notification-card__action vestra-notification-card__action--danger"
            aria-label="Delete notification"
            title="Delete notification"
        >
            <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
        </button>
    </div>
</div>
