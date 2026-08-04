@props([
    'product',
    'selectedIds' => [],
])

@php
use App\Enums\ProductStatus;

$statusValue = $product->status instanceof ProductStatus ? $product->status->value : (string) $product->status;
$primaryImage = $product->images->first();
$imageUrl = $primaryImage ? asset('storage/'.$primaryImage->image) : asset('images/placeholder.svg');
$isSelected = in_array($product->id, $selectedIds, true);
@endphp

<tr class="vestra-products__row" wire:key="product-{{ $product->id }}">
    <td class="vestra-products__td vestra-products__td--select">
        <input
            type="checkbox"
            class="vestra-products__filter-checkbox"
            wire:model.live="selectedIds"
            value="{{ $product->id }}"
            @checked($isSelected)
            aria-label="Select product {{ $product->name }}"
        />
    </td>

    <td class="vestra-products__td vestra-products__td--product">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $product->id }})"
            class="vestra-products__product-link"
        >
            <img
                src="{{ $imageUrl }}"
                alt=""
                class="vestra-products__thumb"
                loading="lazy"
                onerror="this.src='{{ asset('images/placeholder.svg') }}'"
            />
            <span class="vestra-products__product-text">
                <span class="vestra-products__product-name">{{ $product->name }}</span>
                @if ($product->short_description)
                    <span class="vestra-products__row-meta">{{ \Illuminate\Support\Str::limit($product->short_description, 60) }}</span>
                @endif
            </span>
        </button>
    </td>

    <td class="vestra-products__td vestra-products__td--sku">
        <span class="vestra-products__sku">{{ $product->sku ?: '—' }}</span>
    </td>

    <td class="vestra-products__td vestra-products__td--category">
        <span class="vestra-products__cell-text">{{ $product->category?->name ?? '—' }}</span>
    </td>

    <td class="vestra-products__td vestra-products__td--price">
        <span class="vestra-products__price">{{ number_format((float) $product->price, 2) }}</span>
        @if ($product->distributor_price !== null)
            <span class="vestra-products__row-meta">Dist. {{ number_format((float) $product->distributor_price, 2) }}</span>
        @endif
    </td>

    <td class="vestra-products__td vestra-products__td--stock">
        <x-products.stock-badge
            :quantity="$product->stock_quantity"
            :label="$product->stockStatusLabel()"
            :color="$product->stockStatusColor()"
        />
    </td>

    <td class="vestra-products__td vestra-products__td--status">
        <x-products.status-badge :status="$statusValue" />
    </td>

    <td class="vestra-products__td vestra-products__td--featured">
        @if ($product->featured)
            <span class="vestra-products__featured-pill">Featured</span>
        @else
            <span class="vestra-products__empty-cell">—</span>
        @endif
    </td>

    <td class="vestra-products__td vestra-products__td--updated">
        <span class="vestra-products__created">{{ $product->updated_at?->format('M j, Y') ?? '—' }}</span>
        <span class="vestra-products__row-meta">{{ $product->updated_at?->format('g:i A') }}</span>
    </td>

    <td class="vestra-products__td vestra-products__td--actions">
        <div class="vestra-products__actions" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-products__action-trigger" aria-label="Product actions" aria-haspopup="true">
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-products__action-menu" role="menu">
                <button
                    type="button"
                    wire:click="openDetailDrawer({{ $product->id }})"
                    class="vestra-products__action-item"
                    role="menuitem"
                >
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View Details</span>
                </button>
            </div>
        </div>
    </td>
</tr>
