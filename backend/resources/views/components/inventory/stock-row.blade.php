@props(['stock'])

@php
$product = $stock->product;
$warehouse = $stock->warehouse;
$available = $stock->availableQuantity();
$value = (float) $stock->quantity * (float) ($product?->price ?? 0);
$status = app(\App\Services\Admin\InventoryAdminService::class)->resolveStockStatus($stock);
$image = $product?->images?->first();
$imageUrl = $image?->image ? asset('storage/'.$image->image) : null;
@endphp

<tr class="vestra-inventory__row" wire:key="stock-{{ $stock->id }}">
    <td class="vestra-inventory__td vestra-inventory__td--product">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $stock->id }})"
            class="vestra-inventory__product-cell"
        >
            <span class="vestra-inventory__thumb">
                @if ($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $product?->name }}" class="vestra-inventory__thumb-img" />
                @else
                    <x-filament::icon icon="heroicon-o-cube" class="h-4 w-4" />
                @endif
            </span>
            <span class="vestra-inventory__product-text">
                <span class="vestra-inventory__product-name">{{ $product?->name ?? '—' }}</span>
                @if ($product?->category)
                    <span class="vestra-inventory__row-meta">{{ $product->category->name }}</span>
                @endif
            </span>
        </button>
    </td>

    <td class="vestra-inventory__td vestra-inventory__td--sku">
        <span class="vestra-inventory__sku">{{ $product?->sku ?? '—' }}</span>
    </td>

    <td class="vestra-inventory__td vestra-inventory__td--warehouse">
        <span class="vestra-inventory__warehouse-name">{{ $warehouse?->name ?? '—' }}</span>
        @if ($warehouse?->code)
            <span class="vestra-inventory__row-meta">{{ $warehouse->code }}</span>
        @endif
    </td>

    <td class="vestra-inventory__td vestra-inventory__td--quantity">
        <span class="vestra-inventory__num">{{ number_format($stock->quantity) }}</span>
    </td>

    <td class="vestra-inventory__td vestra-inventory__td--available">
        <span class="vestra-inventory__num">{{ number_format($available) }}</span>
    </td>

    <td class="vestra-inventory__td vestra-inventory__td--reserved">
        <span class="vestra-inventory__num">{{ number_format($stock->reserved_quantity) }}</span>
    </td>

    <td class="vestra-inventory__td vestra-inventory__td--value">
        <span class="vestra-inventory__num">UGX {{ number_format($value, 0) }}</span>
    </td>

    <td class="vestra-inventory__td vestra-inventory__td--status">
        <x-inventory.status-badge :status="$status['key']" :label="$status['label']" :color="$status['color']" />
    </td>

    <td class="vestra-inventory__td vestra-inventory__td--updated">
        <span class="vestra-inventory__created">{{ $stock->updated_at?->format('M j, Y') ?? '—' }}</span>
        <span class="vestra-inventory__row-meta">{{ $stock->updated_at?->format('g:i A') }}</span>
    </td>

    <td class="vestra-inventory__td vestra-inventory__td--actions">
        <div class="vestra-inventory__actions" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-inventory__action-trigger" aria-label="Stock actions" aria-haspopup="true">
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-inventory__action-menu" role="menu">
                <button
                    type="button"
                    wire:click="openDetailDrawer({{ $stock->id }})"
                    class="vestra-inventory__action-item"
                    role="menuitem"
                >
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View Details</span>
                </button>
            </div>
        </div>
    </td>
</tr>
