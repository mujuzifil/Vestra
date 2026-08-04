@props(['status' => null])

@php
use App\Enums\ContactStatus;

$resolved = $status instanceof ContactStatus ? $status : ContactStatus::tryFrom((string) $status);
$label    = $resolved?->label() ?? ucfirst((string) $status);
$color    = $resolved?->color() ?? 'gray';
@endphp

<span class="vestra-enquiries__badge vestra-enquiries__badge--{{ $color }}">{{ $label }}</span>
