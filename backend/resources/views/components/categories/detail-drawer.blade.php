@props([
    'show' => false,
    'category' => null,
])

@php
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ProductResource;
@endphp

<div
    class="vestra-categories-detail @if ($show) vestra-categories-detail--open @endif"
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
    aria-label="Category details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-categories-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-categories-detail__panel">
        @if ($category)
            @php
                $products = $category['products'] ?? [];
                $categoryModel = \App\Models\Category::query()->find($category['id']);
                $canUpdate = $categoryModel && (auth()->user()?->can('update', $categoryModel) ?? false);
                $editUrl = $canUpdate
                    ? CategoryResource::getUrl('edit', ['record' => $category['id']])
                    : null;
            @endphp

            <div class="vestra-categories-detail__header">
                <div class="vestra-categories-detail__header-main">
                    <span class="vestra-categories-detail__avatar">
                        <x-filament::icon icon="heroicon-o-tag" class="h-5 w-5" />
                    </span>
                    <div class="vestra-categories-detail__header-text">
                        <h2 class="vestra-categories-detail__title">{{ $category['name'] ?? 'Category' }}</h2>
                        <p class="vestra-categories-detail__subtitle">
                            {{ $category['slug'] ?? '—' }}
                            &middot;
                            {{ number_format($category['products_count'] ?? 0) }} products
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-categories-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-categories-detail__body">
                <div class="vestra-categories-detail__badges">
                    <x-categories.status-badge :status="$category['status'] ?? null" />
                </div>

                @if ($editUrl)
                    <div class="vestra-categories-detail__quick-actions">
                        <a href="{{ $editUrl }}" class="vestra-categories-detail__quick-action">
                            <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                            <span>Edit Category</span>
                        </a>
                    </div>
                @endif

                <div class="vestra-categories-detail__section">
                    <h3 class="vestra-categories-detail__section-title">Description</h3>
                    <p class="vestra-categories-detail__message">
                        {{ filled($category['description'] ?? null) ? $category['description'] : 'No description provided.' }}
                    </p>
                </div>

                <div class="vestra-categories-detail__section">
                    <h3 class="vestra-categories-detail__section-title">Details</h3>
                    <dl class="vestra-categories-detail__definition-list">
                        <div class="vestra-categories-detail__definition-row">
                            <dt>Name</dt>
                            <dd>{{ $category['name'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-categories-detail__definition-row">
                            <dt>Slug</dt>
                            <dd><code>{{ $category['slug'] ?? '—' }}</code></dd>
                        </div>
                        <div class="vestra-categories-detail__definition-row">
                            <dt>Sort Order</dt>
                            <dd>{{ $category['sort_order'] ?? 0 }}</dd>
                        </div>
                        <div class="vestra-categories-detail__definition-row">
                            <dt>Status</dt>
                            <dd>{{ $category['status_label'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-categories-detail__definition-row">
                            <dt>Products</dt>
                            <dd>{{ number_format($category['products_count'] ?? 0) }}</dd>
                        </div>
                        <div class="vestra-categories-detail__definition-row">
                            <dt>Created</dt>
                            <dd>{{ isset($category['created_at']) ? $category['created_at']->format('M j, Y g:i A') : '—' }}</dd>
                        </div>
                        <div class="vestra-categories-detail__definition-row">
                            <dt>Last Updated</dt>
                            <dd>{{ isset($category['updated_at']) ? $category['updated_at']->format('M j, Y g:i A') : '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-categories-detail__section">
                    <h3 class="vestra-categories-detail__section-title">Assigned Products</h3>
                    @if (count($products) > 0)
                        <ul class="vestra-categories-detail__product-list" role="list">
                            @foreach ($products as $product)
                                <li class="vestra-categories-detail__product-item">
                                    <div class="vestra-categories-detail__product-main">
                                        <a
                                            href="{{ ProductResource::getUrl('edit', ['record' => $product['id']]) }}"
                                            class="vestra-categories-detail__product-name"
                                        >
                                            {{ $product['name'] }}
                                        </a>
                                        <span class="vestra-categories-detail__product-meta">
                                            {{ $product['sku'] ?? '—' }}
                                            &middot;
                                            {{ $product['status_label'] ?? '—' }}
                                        </span>
                                    </div>
                                    <span class="vestra-categories-detail__product-stock">
                                        Stock: {{ number_format($product['stock_quantity'] ?? 0) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-categories-detail__text">No products assigned to this category.</p>
                    @endif
                </div>
            </div>
        @else
            <div class="vestra-categories-detail__empty">
                <p>Select a category to view details.</p>
            </div>
        @endif
    </div>
</div>
