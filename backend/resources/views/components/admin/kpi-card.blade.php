@props([
    'icon' => 'heroicon-o-document-text',
    'label' => '',
    'value' => '0',
    'trend' => '0%',
    'trendLabel' => '',
    'trendPositive' => true,
    'trendAvailable' => true,
    'color' => 'primary',
])

@php
$colorMap = [
    'primary' => ['bg' => 'var(--primary-100)', 'text' => 'var(--primary-600)', 'light' => 'var(--primary-50)'],
    'success' => ['bg' => 'var(--success-100)', 'text' => 'var(--success-600)', 'light' => 'var(--success-50)'],
    'danger' => ['bg' => 'var(--danger-100)', 'text' => 'var(--danger-600)', 'light' => 'var(--danger-50)'],
    'warning' => ['bg' => 'var(--warning-100)', 'text' => 'var(--warning-600)', 'light' => 'var(--warning-50)'],
    'info' => ['bg' => 'var(--info-100)', 'text' => 'var(--info-600)', 'light' => 'var(--info-50)'],
];
$style = $colorMap[$color] ?? $colorMap['primary'];

$trendValue = trim((string) $trend);
$trendClass = match (true) {
    str_starts_with($trendValue, '+') || str_starts_with($trendValue, 'Up') => 'vestra-kpi-card__trend--up',
    str_starts_with($trendValue, '-') || str_starts_with($trendValue, 'Down') => 'vestra-kpi-card__trend--down',
    default => 'vestra-kpi-card__trend--neutral',
};
@endphp

<div class="vestra-kpi-card">
    <div class="vestra-kpi-card__main">
        <span class="vestra-kpi-card__icon" style="background-color: {{ $style['light'] }}; color: {{ $style['text'] }}">
            <x-filament::icon :icon="$icon" class="h-5 w-5" />
        </span>

        <div class="vestra-kpi-card__content">
            <p class="vestra-kpi-card__label">{{ $label }}</p>
            <p class="vestra-kpi-card__value">{{ $value }}</p>
        </div>
    </div>

    @if ($trendAvailable)
        <span class="vestra-kpi-card__trend {{ $trendClass }}">
            @if ($trendClass === 'vestra-kpi-card__trend--up')
                <x-filament::icon icon="heroicon-m-arrow-trending-up" class="h-3 w-3" />
            @elseif ($trendClass === 'vestra-kpi-card__trend--down')
                <x-filament::icon icon="heroicon-m-arrow-trending-down" class="h-3 w-3" />
            @else
                <x-filament::icon icon="heroicon-m-minus" class="h-3 w-3" />
            @endif
            <span>{{ $trend }}</span>
            @if ($trendLabel)
                <span class="vestra-kpi-card__trend-label">{{ $trendLabel }}</span>
            @endif
        </span>
    @endif
</div>
