@props([
    'show' => false,
    'product' => null,
])

<div
    class="vestra-products-detail @if ($show) vestra-products-detail--open @endif"
    x-data="{ open: @js($show) }"
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
                        <h2 class="vestra-products-detail__title">{{ $product['name'] ?? 'Product' }}</h2>
                        <p class="vestra-products-detail__subtitle">{{ $product['sku'] ?? '' }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-products-detail__close"
                    aria-label="Close details"
                >
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

                <div class="vestra-products-detail__section">
                    <h3 class="vestra-products-detail__section-title">Details</h3>
                    <dl class="vestra-products-detail__definition-list">
                        <div class="vestra-products-detail__definition-row">
                            <dt>Name</dt>
                            <dd>{{ $product['name'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-products-detail__definition-row">
                            <dt>SKU</dt>
                            <dd>{{ $product['sku'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-products-detail__definition-row">
                            <dt>Slug</dt>
                            <dd>{{ $product['slug'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-products-detail__definition-row">
                            <dt>Created</dt>
                            <dd>{{ $product['created_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="vestra-products-detail__definition-row">
                            <dt>Updated</dt>
                            <dd>{{ $product['updated_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                    </dl>
                    @if (! empty($product['short_description']))
                        <p class="vestra-products-detail__text">{{ $product['short_description'] }}</p>
                    @endif
                </div>

                <div class="vestra-products-detail__section">
                    <h3 class="vestra-products-detail__section-title">Pricing</h3>
                    <dl class="vestra-products-detail__definition-list">
                        <div class="vestra-products-detail__definition-row">
                            <dt>Retail Price</dt>
                            <dd>{{ number_format((float) ($product['price'] ?? 0), 2) }}</dd>
                        </div>
                        <div class="vestra-products-detail__definition-row">
                            <dt>Distributor Price</dt>
                            <dd>
                                @if (($product['distributor_price'] ?? null) !== null)
                                    {{ number_format((float) $product['distributor_price'], 2) }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-products-detail__section">
                    <h3 class="vestra-products-detail__section-title">Stock</h3>
                    <dl class="vestra-products-detail__definition-list">
                        <div class="vestra-products-detail__definition-row">
                            <dt>Quantity</dt>
                            <dd>{{ number_format((int) ($product['stock_quantity'] ?? 0)) }}</dd>
                        </div>
                        <div class="vestra-products-detail__definition-row">
                            <dt>Status</dt>
                            <dd>{{ $product['stock_status_label'] ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-products-detail__section">
                    <h3 class="vestra-products-detail__section-title">Category</h3>
                    @if ($product['category'] ?? null)
                        <p class="vestra-products-detail__text">{{ $product['category']['name'] }}</p>
                    @else
                        <p class="vestra-products-detail__text">No category assigned.</p>
                    @endif
                </div>

                <div class="vestra-products-detail__section">
                    <h3 class="vestra-products-detail__section-title">Images</h3>
                    @if (! empty($product['images']))
                        <div class="vestra-products-detail__images">
                            @foreach ($product['images'] as $image)
                                <figure class="vestra-products-detail__image">
                                    <img
                                        src="{{ $image['url'] }}"
                                        alt="{{ $image['alt_text'] ?: ($product['name'] ?? 'Product image') }}"
                                        loading="lazy"
                                        onerror="this.style.display='none'"
                                    />
                                </figure>
                            @endforeach
                        </div>
                    @else
                        <p class="vestra-products-detail__text">No images uploaded.</p>
                    @endif
                </div>

                @if (! empty($product['warehouse_stocks']))
                    <div class="vestra-products-detail__section">
                        <h3 class="vestra-products-detail__section-title">Warehouse Stocks</h3>
                        <ul class="vestra-products-detail__warehouse-list">
                            @foreach ($product['warehouse_stocks'] as $stock)
                                <li class="vestra-products-detail__warehouse-item">
                                    <div>
                                        <p class="vestra-products-detail__contact-name">{{ $stock['warehouse_name'] }}</p>
                                        <p class="vestra-products-detail__contact-meta">
                                            Qty {{ number_format((int) $stock['quantity']) }}
                                            · Available {{ number_format((int) $stock['available']) }}
                                            · Reserved {{ number_format((int) $stock['reserved_quantity']) }}
                                        </p>
                                    </div>
                                    @if ($stock['is_low'])
                                        <span class="vestra-products__stock vestra-products__stock--warning">
                                            <span class="vestra-products__stock-label">Low</span>
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (! empty($product['edit_url']))
                    <div class="vestra-products-detail__section">
                        <a href="{{ $product['edit_url'] }}" class="vestra-button vestra-button--primary">
                            <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                            <span>Edit Product</span>
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
