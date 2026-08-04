@props(['source' => null])

@php
$map = [
    'blog' => 'Blog',
    'product' => 'Product',
    'settings' => 'Settings',
    'spatie' => 'System',
];
$label = $map[$source] ?? ucfirst((string) $source);
@endphp

<span class="vestra-media__source-pill vestra-media__source-pill--{{ $source }}">{{ $label }}</span>
