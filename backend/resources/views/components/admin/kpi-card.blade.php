@props([
    'icon' => 'heroicon-o-document-text',
    'label' => '',
    'value' => '0',
    'trend' => '0%',
    'trendLabel' => '',
    'trendPositive' => true,
    'color' => 'primary',
])

@php
$colorMap = [
    'primary' => ['bg' => 'var(--primary-100)', 'text' => 'var(--primary-600)'],
    'success' => ['bg' => 'var(--success-100)', 'text' => 'var(--success-600)'],
    'danger' => ['bg' => 'var(--danger-100)', 'text' => 'var(--danger-600)'],
    'warning' => ['bg' => 'var(--warning-100)', 'text' => 'var(--warning-600)'],
    'info' => ['bg' => 'var(--info-100)', 'text' => 'var(--info-600)'],
];
$style = $colorMap[$color] ?? $colorMap['primary'];
@endphp

<div class="vestra-kpi-card">
    <span class="vestra-kpi-card__icon" style="background-color: {{ $style['bg'] }}; color: {{ $style['text'] }}">
        <x-filament::icon :icon="$icon" class="h-6 w-6" />
    </span>

    <div class="vestra-kpi-card__content">
        <p class="vestra-kpi-card__label">{{ $label }}</p>
        <p class="vestra-kpi-card__value">{{ $value }}</p>
        <span class="vestra-kpi-card__trend @if($trendPositive) vestra-kpi-card__trend--up @else vestra-kpi-card__trend--down @endif">
            @if ($trendPositive)
                <x-filament::icon icon="heroicon-m-arrow-trending-up" class="h-3.5 w-3.5" />
            @else
                <x-filament::icon icon="heroicon-m-arrow-trending-down" class="h-3.5 w-3.5" />
            @endif
            {{ $trend }}
            <span class="vestra-kpi-card__trend-label">{{ $trendLabel }}</span>
        </span>
    </div>
</div>
