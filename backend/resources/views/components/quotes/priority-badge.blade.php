@props([
    'priority' => null,
])

@php
use App\Enums\QuoteRequestPriority;

$enum = $priority instanceof QuoteRequestPriority
    ? $priority
    : QuoteRequestPriority::tryFromMixed($priority);

$color = $enum?->color() ?? 'gray';
$label = $enum?->label() ?? (filled($priority) ? ucfirst((string) $priority) : '—');
$icon = $enum?->icon() ?? 'heroicon-o-minus';
@endphp

<span class="vestra-quotes__badge vestra-quotes__badge--{{ $color }}">
    <x-filament::icon :icon="$icon" class="h-3.5 w-3.5" />
    {{ $label }}
</span>
