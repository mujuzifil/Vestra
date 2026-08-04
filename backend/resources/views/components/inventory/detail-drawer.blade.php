@props([
    'show' => false,
    'stock' => null,
])

<div
    class="vestra-inventory-detail @if ($show) vestra-inventory-detail--open @endif"
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
    aria-label="Stock details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-inventory-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-inventory-detail__panel">
        @if ($stock)
            <div class="vestra-inventory-detail__header">
                <div class="vestra-inventory-detail__header-main">
                    <span class="vestra-inventory-detail__avatar">
                        @if (! empty($stock['product']['image']))
                            <img src="{{ $stock['product']['image'] }}" alt="" class="vestra-inventory-detail__avatar-img" />
                        @else
                            <x-filament::icon icon="heroicon-o-cube-transparent" class="h-5 w-5" />
                        @endif
                    </span>
                    <div class="vestra-inventory-detail__header-text">
                        <h2 class="vestra-inventory-detail__title">{{ $stock['product']['name'] ?? 'Stock line' }}</h2>
                        <p class="vestra-inventory-detail__subtitle">
                            {{ $stock['product']['sku'] ?? '' }}
                            @if (! empty($stock['warehouse']['name']))
                                · {{ $stock['warehouse']['name'] }}
                            @endif
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-inventory-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-inventory-detail__body">
                <div class="vestra-inventory-detail__badges">
                    <x-inventory.status-badge
                        :status="$stock['stock_status']"
                        :label="$stock['stock_status_label']"
                        :color="$stock['stock_status_color']"
                    />
                </div>

                <div class="vestra-inventory-detail__section">
                    <h3 class="vestra-inventory-detail__section-title">Stock Levels</h3>
                    <dl class="vestra-inventory-detail__definition-list">
                        <div class="vestra-inventory-detail__definition-row">
                            <dt>Quantity</dt>
                            <dd>{{ number_format($stock['quantity'] ?? 0) }}</dd>
                        </div>
                        <div class="vestra-inventory-detail__definition-row">
                            <dt>Available</dt>
                            <dd>{{ number_format($stock['available_quantity'] ?? 0) }}</dd>
                        </div>
                        <div class="vestra-inventory-detail__definition-row">
                            <dt>Reserved</dt>
                            <dd>{{ number_format($stock['reserved_quantity'] ?? 0) }}</dd>
                        </div>
                        <div class="vestra-inventory-detail__definition-row">
                            <dt>Reorder Level</dt>
                            <dd>{{ number_format($stock['reorder_level'] ?? 0) }}</dd>
                        </div>
                        <div class="vestra-inventory-detail__definition-row">
                            <dt>Value</dt>
                            <dd>{{ $stock['value_formatted'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-inventory-detail__definition-row">
                            <dt>Updated</dt>
                            <dd>{{ $stock['updated_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-inventory-detail__section">
                    <h3 class="vestra-inventory-detail__section-title">Product</h3>
                    @if ($stock['product'] ?? null)
                        <dl class="vestra-inventory-detail__definition-list">
                            <div class="vestra-inventory-detail__definition-row">
                                <dt>Name</dt>
                                <dd>{{ $stock['product']['name'] }}</dd>
                            </div>
                            <div class="vestra-inventory-detail__definition-row">
                                <dt>SKU</dt>
                                <dd>{{ $stock['product']['sku'] ?? '—' }}</dd>
                            </div>
                            <div class="vestra-inventory-detail__definition-row">
                                <dt>Category</dt>
                                <dd>{{ $stock['product']['category'] ?? '—' }}</dd>
                            </div>
                            <div class="vestra-inventory-detail__definition-row">
                                <dt>Unit Price</dt>
                                <dd>{{ $stock['product']['price_formatted'] ?? '—' }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="vestra-inventory-detail__text">No product on record.</p>
                    @endif
                </div>

                <div class="vestra-inventory-detail__section">
                    <h3 class="vestra-inventory-detail__section-title">Warehouse</h3>
                    @if ($stock['warehouse'] ?? null)
                        <dl class="vestra-inventory-detail__definition-list">
                            <div class="vestra-inventory-detail__definition-row">
                                <dt>Name</dt>
                                <dd>{{ $stock['warehouse']['name'] }}</dd>
                            </div>
                            <div class="vestra-inventory-detail__definition-row">
                                <dt>Code</dt>
                                <dd>{{ $stock['warehouse']['code'] ?? '—' }}</dd>
                            </div>
                            <div class="vestra-inventory-detail__definition-row">
                                <dt>Address</dt>
                                <dd>{{ $stock['warehouse']['address'] ?: '—' }}</dd>
                            </div>
                            <div class="vestra-inventory-detail__definition-row">
                                <dt>Status</dt>
                                <dd>{{ ($stock['warehouse']['is_active'] ?? false) ? 'Active' : 'Inactive' }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="vestra-inventory-detail__text">No warehouse on record.</p>
                    @endif
                </div>

                <div class="vestra-inventory-detail__section">
                    <h3 class="vestra-inventory-detail__section-title">Adjust Stock</h3>
                    <p class="vestra-inventory-detail__text">Positive values add stock; negative values remove stock.</p>
                    <div class="vestra-inventory-detail__adjust-form">
                        <label class="vestra-inventory__filter-field">
                            <span class="vestra-inventory__filter-field-label">Quantity</span>
                            <input
                                type="number"
                                wire:model="adjustQuantity"
                                class="vestra-inventory__filter-input"
                                placeholder="e.g. 10 or -5"
                                aria-label="Adjustment quantity"
                            />
                        </label>
                        <label class="vestra-inventory__filter-field">
                            <span class="vestra-inventory__filter-field-label">Reason</span>
                            <input
                                type="text"
                                wire:model="adjustReason"
                                class="vestra-inventory__filter-input"
                                maxlength="255"
                                placeholder="e.g. Cycle count correction"
                                aria-label="Adjustment reason"
                            />
                        </label>
                        <button
                            type="button"
                            wire:click="adjustStock"
                            wire:confirm="Apply this stock adjustment?"
                            class="vestra-button vestra-button--primary"
                        >
                            <x-filament::icon icon="heroicon-o-adjustments-horizontal" class="h-4 w-4" />
                            <span>Adjust Stock</span>
                        </button>
                    </div>
                </div>

                <div class="vestra-inventory-detail__section">
                    <h3 class="vestra-inventory-detail__section-title">Recent Movements</h3>
                    @if (! empty($stock['movements']))
                        <ul class="vestra-inventory-detail__movements">
                            @foreach ($stock['movements'] as $movement)
                                <li class="vestra-inventory-detail__movement" wire:key="movement-{{ $movement['id'] }}">
                                    <div class="vestra-inventory-detail__movement-head">
                                        <span class="vestra-inventory__badge vestra-inventory__badge--{{ $movement['type_color'] }}">
                                            {{ $movement['type_label'] }}
                                        </span>
                                        <span class="vestra-inventory-detail__movement-qty">
                                            {{ number_format($movement['quantity']) }}
                                            · bal {{ number_format($movement['balance_after']) }}
                                        </span>
                                    </div>
                                    @if (! empty($movement['reason']))
                                        <p class="vestra-inventory-detail__text">{{ $movement['reason'] }}</p>
                                    @endif
                                    <p class="vestra-inventory__row-meta">
                                        {{ $movement['created_at']?->format('M j, Y g:i A') ?? '—' }}
                                        @if (! empty($movement['user_name']))
                                            · {{ $movement['user_name'] }}
                                        @endif
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-inventory-detail__text">No stock movements recorded for this line.</p>
                    @endif
                </div>
            </div>
        @else
            <div class="vestra-inventory-detail__empty">Select a stock line to view details.</div>
        @endif
    </div>
</div>
