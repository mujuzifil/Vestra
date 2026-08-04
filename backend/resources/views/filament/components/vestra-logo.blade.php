@props([
    'variant' => 'default',
    'height' => '2rem',
])

@php
$src = asset('images/vestra-logo.png');
$alt = 'VESTRA';
$isAdmin = $variant === 'admin';
@endphp

@if ($isAdmin)
    <div class="flex flex-col items-start gap-1">
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            width="857"
            height="474"
            decoding="async"
            style="height: {{ $height }}; width: auto; max-width: 11rem; object-fit: contain; object-position: left center;"
            class="block vestra-logo-admin"
        />
        <span class="text-xs font-medium text-white/70 tracking-wide">Admin Portal</span>
    </div>
@else
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        width="857"
        height="474"
        decoding="async"
        style="height: {{ $height }}; width: auto; object-fit: contain;"
        class="block"
    />
@endif
