@props(['priority' => null])

@php
use App\Enums\Priority;

$enum = Priority::tryFrom((string) $priority);
$label = $enum?->label() ?? ucfirst((string) $priority);
$color = $enum?->color() ?? 'gray';
@endphp

<span class="vestra-feedback__badge vestra-feedback__badge--{{ $color }}">{{ $label }}</span>
