@props([
    'quantity' => 0,
    'label' => 'In Stock',
    'color' => 'success',
])

@php
$class = match ($color) {
    'danger' => 'vestra-products__stock--danger',
    'warning' => 'vestra-products__stock--warning',
    default => 'vestra-products__stock--success',
};
@endphp

<span class="vestra-products__stock {{ $class }}" title="{{ $label }}">
    <span class="vestra-products__stock-qty">{{ number_format((int) $quantity) }}</span>
    <span class="vestra-products__stock-label">{{ $label }}</span>
</span>
