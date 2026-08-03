@props([
    'priority' => null,
])

@php
use App\Enums\Priority;

$enum = $priority instanceof Priority ? $priority : Priority::tryFrom((string) $priority);

$color = $enum?->color() ?? 'gray';
$label = $enum?->label() ?? (filled($priority) ? ucfirst((string) $priority) : '—');
@endphp

<span class="vestra-applications__badge vestra-applications__badge--{{ $color }}">
    <x-filament::icon icon="heroicon-o-flag" class="h-3.5 w-3.5" />
    {{ $label }}
</span>
