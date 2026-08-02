@props([
    'icon' => 'heroicon-o-bell',
    'title' => '',
    'body' => null,
    'time' => '',
    'read' => true,
])

<div class="vestra-notification-item @if(!$read) vestra-notification-item--unread @endif">
    <span class="vestra-notification-item__icon">
        <x-filament::icon :icon="$icon" class="h-5 w-5" />
    </span>
    <div class="vestra-notification-item__content">
        <p class="vestra-notification-item__title">{{ $title }}</p>
        @if ($body)
            <p class="vestra-notification-item__body">{{ $body }}</p>
        @endif
    </div>
    <span class="vestra-notification-item__time">{{ $time }}</span>
</div>
