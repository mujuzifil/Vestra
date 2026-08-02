@props([
    'icon' => 'heroicon-o-bolt',
    'color' => 'gray',
    'title' => '',
    'subtitle' => '',
    'time' => '',
    'url' => null,
])

@php
$colorMap = [
    'primary' => ['bg' => 'var(--primary-100)', 'text' => 'var(--primary-600)'],
    'success' => ['bg' => 'var(--success-100)', 'text' => 'var(--success-600)'],
    'danger' => ['bg' => 'var(--danger-100)', 'text' => 'var(--danger-600)'],
    'warning' => ['bg' => 'var(--warning-100)', 'text' => 'var(--warning-600)'],
    'info' => ['bg' => 'var(--info-100)', 'text' => 'var(--info-600)'],
    'gray' => ['bg' => 'var(--neutral-100)', 'text' => 'var(--neutral-500)'],
];
$style = $colorMap[$color] ?? $colorMap['gray'];
@endphp

@if ($url)
    <a href="{{ $url }}" class="vestra-activity-item group">
@else
    <div class="vestra-activity-item">
@endif
    <span class="vestra-activity-item__icon" style="background-color: {{ $style['bg'] }}; color: {{ $style['text'] }}">
        <x-filament::icon :icon="$icon" class="h-5 w-5" />
    </span>
    <div class="vestra-activity-item__content">
        <p class="vestra-activity-item__title">{{ $title }}</p>
        @if ($subtitle)
            <p class="vestra-activity-item__subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    <span class="vestra-activity-item__time">{{ $time }}</span>
@if ($url)
    </a>
@else
    </div>
@endif
