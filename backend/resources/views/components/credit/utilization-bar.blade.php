@props([
    'percentage' => 0,
])

@php
$percentage = max(0, min(100, (float) $percentage));
$tone = match (true) {
    $percentage >= 90 => 'danger',
    $percentage >= 70 => 'warning',
    default => 'success',
};
@endphp

<div class="vestra-credit__utilization" role="progressbar" aria-valuenow="{{ round($percentage, 1) }}" aria-valuemin="0" aria-valuemax="100">
    <div class="vestra-credit__utilization-track">
        <div class="vestra-credit__utilization-fill vestra-credit__utilization-fill--{{ $tone }}" style="width: {{ $percentage }}%"></div>
    </div>
    <span class="vestra-credit__utilization-label">{{ number_format($percentage, 1) }}%</span>
</div>
