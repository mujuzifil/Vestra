@props(['status' => null])

@php
$status = (string) ($status ?? 'pending');
$color = match ($status) {
    'active' => 'success',
    'pending' => 'warning',
    'suspended' => 'danger',
    default => 'gray',
};
@endphp

<span class="vestra-credit__badge vestra-credit__badge--{{ $color }}">{{ ucfirst($status) }}</span>
