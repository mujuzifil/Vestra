@props([
    'status' => null,
])

@php
$color = $status?->color() ?? 'gray';
$label = $status?->label() ?? 'Unknown';
@endphp

<span class="vestra-partners__badge vestra-partners__badge--{{ $color }}">
    {{ $label }}
</span>
