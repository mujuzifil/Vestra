@props([
    'status' => null,
])

@php
use App\Enums\DistributorStatus;

$color = $status?->color() ?? 'gray';
$label = $status?->label() ?? 'Unknown';
$icon = match ($status) {
    DistributorStatus::PENDING => 'heroicon-o-clock',
    DistributorStatus::UNDER_REVIEW => 'heroicon-o-magnifying-glass',
    DistributorStatus::INFORMATION_REQUESTED => 'heroicon-o-question-mark-circle',
    DistributorStatus::APPROVED => 'heroicon-o-check-circle',
    DistributorStatus::REJECTED => 'heroicon-o-x-circle',
    default => 'heroicon-o-question-mark-circle',
};
@endphp

<span class="vestra-applications__badge vestra-applications__badge--{{ $color }}">
    <x-filament::icon :icon="$icon" class="h-3.5 w-3.5" />
    {{ $label }}
</span>
