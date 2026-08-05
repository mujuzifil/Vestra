@props([
    'show' => false,
    'product' => null,
    'canEdit' => false,
])

@php
$display = function ($value, string $fallback = 'Not provided') {
    if ($value === null) {
        return $fallback;
    }

    if (is_string($value) && trim($value) === '') {
        return $fallback;
    }

    return $value;
};
@endphp

<div
    class="vestra-products-detail @if ($show) vestra-products-detail--open @endif"
    x-data="{ open: @entangle('showDetailDrawer') }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) $wire.closeDetailDrawer()"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-4"
    aria-label="Product details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-products-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-products-detail__panel">
        @if ($product)
            <div class="vestra-products-detail__header">
                <div class="vestra-products-detail__header-main">
                    <span class="vestra-products-detail__avatar">
                        <x-filament::icon icon="heroicon-o-shopping-bag" class="h-5 w-5" />
                    </span>
                    <div class="vestra-products-detail__header-text">
                        <h2 class="vestra-products-detail__title">{{ $display($product['name'] ?? null, 'Product') }}</h2>
                        <p class="vestra-products-detail__subtitle">{{ $display($product['sku'] ?? null) }}</p>
                    </div>
                </div>

                <button type="button" wire:click="closeDetailDrawer" class="vestra-products-detail__close" aria-label="Close details">
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-products-detail__body">
                <div class="vestra-products-detail__badges">
                    <x-products.status-badge :status="$product['status'] ?? null" />
                    @if ($product['featured'] ?? false)
                        <span class="vestra-products__featured-pill">Featured</span>
                    @endif
                </div>

                @if ($canEdit)
                    <div class="vestra-products-detail__quick-actions">
                        <button type="button" wire:click="openEditModal({{ $product['id'] }})" class="vestra-products-detail__quick-action vestra-products-detail__quick-action--success">
                            <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                            <span>Edit Product</span>
                        </button>
                    </div>
                @endif

                <div class="vestra-products-detail__section">
                    <h3 class="vestra-products-detail__section-title">General</h3>
                    <dl class="vestra-products-detail__definition-list">
                        <div class="vestra-products-detail__definition-row"><dt>Product Name</dt><dd>{{ $display($product['name'] ?? null) }}</dd></div>
                        <div class="vestra-products-detail__definition-row"><dt>SKU</dt><dd>{{ $display($product['sku'] ?? null) }}</dd></div>
                        <div class="vestra-products-detail__definition-row"><dt>Category</dt><dd>{{ $display($product['category']['name'] ?? null) }}</dd></div>
                        <div class="vestra-products-detail__definition-row"><dt>Product Status</dt><dd>{{ $display($product['status_label'] ?? null) }}</dd></div>
                        <div class="vestra-products-detail__definition-row"><dt>Featured</dt><dd>{{ ($product['featured'] ?? false) ? 'Yes' : 'No' }}</dd></div>
                    </dl>
                </div>

                <div class="vestra-products-detail__section">
                    <h3 class="vestra-products-detail__section-title">Pricing</h3>
                    <dl class="vestra-products-detail__definition-list">
                        <div class="vestra-products-detail__definition-row">
                            <dt>Selling Price</dt>
                            <dd>
                                @if (($product['price'] ?? null) !== null)
                                    {{ number_format((float) $product['price'], 2) }}{{ filled($product['currency'] ?? null) ? ' '.$product['currency'] : '' }}
                                @else
                                    Not provided
                                @endif
                            </dd>
                        </div>
                        <div class="vestra-products-detail__definition-row">
                            <dt>Cost Price</dt>
                            <dd>
                                @if (($product['cost_price'] ?? null) !== null)
                                    {{ number_format((float) $product['cost_price'], 2) }}{{ filled($product['cost_currency'] ?? null) ? ' '.$product['cost_currency'] : '' }}
                                @else
                                    Not provided
                                @endif
                            </dd>
                        </div>
                        <div class="vestra-products-detail__definition-row">
                            <dt>Tax Rate</dt>
                            <dd>{{ ($product['tax_rate'] ?? null) !== null ? number_format((float) $product['tax_rate'], 2).'%' : 'Not provided' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-products-detail__section">
                    <h3 class="vestra-products-detail__section-title">Inventory</h3>
                    <dl class="vestra-products-detail__definition-list">
                        <div class="vestra-products-detail__definition-row"><dt>Stock Quantity</dt><dd>{{ number_format((int) ($product['stock_quantity'] ?? 0)) }}</dd></div>
                        <div class="vestra-products-detail__definition-row"><dt>Low Stock Threshold</dt><dd>{{ $display($product['low_stock_threshold'] ?? null) }}</dd></div>
                        <div class="vestra-products-detail__definition-row"><dt>Current Stock Status</dt><dd>{{ $display($product['stock_status_label'] ?? null) }}</dd></div>
                        <div class="vestra-products-detail__definition-row"><dt>Unit</dt><dd>{{ $display($product['unit'] ?? null) }}</dd></div>
                        <div class="vestra-products-detail__definition-row"><dt>Weight</dt><dd>{{ ($product['weight'] ?? null) !== null ? $product['weight'].' kg' : 'Not provided' }}</dd></div>
                        <div class="vestra-products-detail__definition-row"><dt>Barcode</dt><dd>{{ $display($product['barcode'] ?? null) }}</dd></div>
                    </dl>
                </div>

                <div class="vestra-products-detail__section">
                    <h3 class="vestra-products-detail__section-title">Product Information</h3>
                    <dl class="vestra-products-detail__definition-list">
                        <div class="vestra-products-detail__definition-row"><dt>Short Description</dt><dd>{{ $display($product['short_description'] ?? null) }}</dd></div>
                        <div class="vestra-products-detail__definition-row"><dt>Full Description</dt><dd>{{ $display($product['description'] ?? null) }}</dd></div>
                    </dl>
                </div>

                <div class="vestra-products-detail__section">
                    <h3 class="vestra-products-detail__section-title">Images</h3>
                    @if (! empty($product['images']))
                        <div class="vestra-products-detail__images">
                            @foreach ($product['images'] as $image)
                                <figure class="vestra-products-detail__image">
                                    <a href="{{ $image['url'] }}" target="_blank" rel="noopener noreferrer">
                                        <img
                                            src="{{ $image['url'] }}"
                                            alt="{{ $image['alt_text'] ?: ($product['name'] ?? 'Product image') }}"
                                            loading="lazy"
                                            onerror="this.style.display='none'"
                                        />
                                    </a>
                                </figure>
                            @endforeach
                        </div>
                    @else
                        <p class="vestra-products-detail__text">Not provided</p>
                    @endif
                </div>

                <div class="vestra-products-detail__section">
                    <h3 class="vestra-products-detail__section-title">Audit</h3>
                    <dl class="vestra-products-detail__definition-list">
                        <div class="vestra-products-detail__definition-row"><dt>Created By</dt><dd>{{ $display($product['created_by'] ?? null) }}</dd></div>
                        <div class="vestra-products-detail__definition-row"><dt>Created Date</dt><dd>{{ $product['created_at']?->format('M j, Y g:i A') ?? 'Not provided' }}</dd></div>
                        <div class="vestra-products-detail__definition-row"><dt>Last Updated</dt><dd>{{ $product['updated_at']?->format('M j, Y g:i A') ?? 'Not provided' }}</dd></div>
                        <div class="vestra-products-detail__definition-row"><dt>Last Updated By</dt><dd>{{ $display($product['updated_by'] ?? null) }}</dd></div>
                    </dl>
                </div>
            </div>
        @else
            <div class="vestra-products-detail__empty">
                <p>Select a product to view details.</p>
            </div>
        @endif
    </div>
</div>
