@props(['status' => null])

@php
$label = ucfirst((string) $status);
$color = match ((string) $status) {
    'active' => 'success',
    'inactive' => 'danger',
    default => 'gray',
};
$icon = match ((string) $status) {
    'active' => 'heroicon-o-check-circle',
    'inactive' => 'heroicon-o-x-circle',
    default => 'heroicon-o-question-mark-circle',
};
@endphp

<span class="vestra-categories__badge vestra-categories__badge--{{ $color }}">
    <x-filament::icon :icon="$icon" class="h-3.5 w-3.5" />
    {{ $label }}
</span>
