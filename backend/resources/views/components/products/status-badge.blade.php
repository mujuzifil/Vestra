@props(['status' => null])

@php
$map = [
    'active' => ['label' => 'Active', 'class' => 'vestra-products__badge--active'],
    'inactive' => ['label' => 'Inactive', 'class' => 'vestra-products__badge--inactive'],
    'out_of_stock' => ['label' => 'Out of Stock', 'class' => 'vestra-products__badge--out-of-stock'],
];
$info = $map[$status] ?? ['label' => ucfirst(str_replace('_', ' ', $status ?? '—')), 'class' => ''];
@endphp

<span class="vestra-products__badge {{ $info['class'] }}">{{ $info['label'] }}</span>
