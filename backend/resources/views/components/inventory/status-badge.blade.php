@props([
    'status' => 'in',
    'label' => null,
    'color' => null,
])

@php
$defaults = [
    'in' => ['label' => 'In Stock', 'color' => 'success'],
    'low' => ['label' => 'Low Stock', 'color' => 'warning'],
    'out' => ['label' => 'Out of Stock', 'color' => 'danger'],
];

$resolvedLabel = $label ?? ($defaults[$status]['label'] ?? ucfirst((string) $status));
$resolvedColor = $color ?? ($defaults[$status]['color'] ?? 'gray');
@endphp

<span class="vestra-inventory__badge vestra-inventory__badge--{{ $resolvedColor }}">
    {{ $resolvedLabel }}
</span>
