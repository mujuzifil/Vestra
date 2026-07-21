@props([
    'variant' => 'dark',
    'height' => '2rem',
])

@php
$src = asset('images/vestra-logo.png');
$alt = 'VESTRA';
@endphp

<img
    src="{{ $src }}"
    alt="{{ $alt }}"
    style="height: {{ $height }}; width: auto; object-fit: contain;"
    class="block"
/>
