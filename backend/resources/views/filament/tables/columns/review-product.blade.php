@php
    $product = $getRecord()->product;
@endphp

<div class="flex items-center gap-3">
    <div class="fi-vestra-avatar h-9 w-9 text-xs">
        {{ $product?->name ? strtoupper(substr($product->name, 0, 1)) : '?' }}
    </div>
    <div>
        <p class="text-sm font-semibold text-neutral-900">{{ $product?->name ?? 'Unknown Product' }}</p>
        <p class="text-xs text-neutral-500">{{ $product?->sku ?? '' }}</p>
    </div>
</div>
