@props([
    'value' => '',
    'color' => 'gray',
])

@php
$colorMap = [
    'primary' => ['bg' => 'var(--primary-100)', 'text' => 'var(--primary-700)'],
    'success' => ['bg' => 'var(--success-100)', 'text' => 'var(--success-700)'],
    'danger' => ['bg' => 'var(--danger-100)', 'text' => 'var(--danger-700)'],
    'warning' => ['bg' => 'var(--warning-100)', 'text' => 'var(--warning-700)'],
    'info' => ['bg' => 'var(--info-100)', 'text' => 'var(--info-700)'],
    'gray' => ['bg' => 'var(--neutral-100)', 'text' => 'var(--neutral-700)'],
    'purple' => ['bg' => 'var(--primary-100)', 'text' => 'var(--primary-700)'],
];
$style = $colorMap[$color] ?? $colorMap['gray'];
@endphp

<span
    class="vestra-notifications__badge"
    style="background-color: {{ $style['bg'] }}; color: {{ $style['text'] }}"
>
    {{ $value }}
</span>
