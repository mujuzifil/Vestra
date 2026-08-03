@props([
    'status' => null,
])

@php
$color = $status?->color() ?? 'gray';
$label = $status?->label() ?? 'Unknown';
$icon = $status?->icon() ?? 'heroicon-o-question-mark-circle';
@endphp

<span class="vestra-quotes__badge vestra-quotes__badge--{{ $color }}">
    <x-filament::icon :icon="$icon" class="h-3.5 w-3.5" />
    {{ $label }}
</span>
