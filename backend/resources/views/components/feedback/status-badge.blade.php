@props(['status' => null])

@php
use App\Enums\FeedbackStatus;

$enum = FeedbackStatus::tryFrom((string) $status);
$label = $enum?->label() ?? ucfirst((string) $status);
$color = $enum?->color() ?? 'gray';

$iconMap = [
    'new' => 'heroicon-o-inbox',
    'in_progress' => 'heroicon-o-arrow-path',
    'resolved' => 'heroicon-o-check-circle',
];
$icon = $iconMap[$status] ?? 'heroicon-o-question-mark-circle';
@endphp

<span class="vestra-feedback__badge vestra-feedback__badge--{{ $color }}">
    <x-filament::icon :icon="$icon" class="h-3.5 w-3.5" />
    {{ $label }}
</span>
