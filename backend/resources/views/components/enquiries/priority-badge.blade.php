@props(['priority' => null])

@php
use App\Enums\Priority;

$resolved = Priority::tryFrom((string) $priority);
$label    = $resolved?->label() ?? ($priority ? ucfirst((string) $priority) : '—');
$color    = $resolved?->color() ?? 'gray';
@endphp

@if ($priority)
    <span class="vestra-enquiries__badge vestra-enquiries__badge--{{ $color }}">{{ $label }}</span>
@else
    <span class="vestra-enquiries__empty-cell">—</span>
@endif
