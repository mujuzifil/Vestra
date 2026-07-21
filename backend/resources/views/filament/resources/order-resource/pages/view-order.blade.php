@php
    use App\Enums\OrderStatus;
    use App\Enums\PaymentStatus;

    $order = $this->getRecord();
    $customer = $order->user;
    $shipping = $order->shipping_address ?? [];
    $timeline = $order->timeline();
    $transaction = $order->latestPaymentTransaction();
@endphp

<x-filament-panels::page>
    <div class="fi-vestra-order-detail space-y-6">

        {{-- Order Summary --}}
        <x-filament::section icon="heroicon-o-clipboard-document-list" heading="Order Summary">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Invoice</p>
                    <p class="mt-1 text-lg font-semibold text-neutral-900">{{ $order->formattedInvoice() }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Order Date</p>
                    <p class="mt-1 text-sm text-neutral-800">{{ $order->created_at?->format('M d, Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Order Status</p>
                    <div class="mt-1 inline-flex">
                        <x-filament::badge :color="OrderStatus::tryFrom($order->status)?->color() ?? 'gray'">
                            {{ OrderStatus::tryFrom($order->status)?->label() ?? $order->status }}
                        </x-filament::badge>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Payment Status</p>
                    <div class="mt-1 inline-flex">
                        <x-filament::badge :color="PaymentStatus::tryFrom($order->payment_status)?->color() ?? 'gray'">
                            {{ PaymentStatus::tryFrom($order->payment_status)?->label() ?? $order->payment_status }}
                        </x-filament::badge>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-3">
                <div class="rounded-lg bg-neutral-50 p-4">
                    <p class="text-xs text-neutral-500">Subtotal</p>
                    <p class="mt-1 text-sm font-medium text-neutral-900">{{ number_format($order->subtotal, 2) }} UGX</p>
                </div>
                <div class="rounded-lg bg-neutral-50 p-4">
                    <p class="text-xs text-neutral-500">Shipping & Tax</p>
                    <p class="mt-1 text-sm font-medium text-neutral-900">{{ number_format($order->shipping_cost + $order->tax_amount, 2) }} UGX</p>
                </div>
                <div class="rounded-lg bg-primary-50 p-4">
                    <p class="text-xs text-primary-600">Order Total</p>
                    <p class="mt-1 text-lg font-semibold text-primary-700">{{ number_format($order->total_amount, 2) }} UGX</p>
                </div>
            </div>
        </x-filament::section>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Customer Information --}}
            <x-filament::section icon="heroicon-o-user" heading="Customer Information" class="lg:col-span-1">
                @if ($customer)
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-semibold text-neutral-900">{{ $customer->name }}</p>
                            <p class="text-sm text-neutral-600">{{ $customer->email }}</p>
                            @if ($customer->phone)
                                <p class="text-sm text-neutral-600">{{ $customer->phone }}</p>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-neutral-100">
                            <div>
                                <p class="text-xs text-neutral-500">Lifetime Orders</p>
                                <p class="text-sm font-medium text-neutral-900">{{ $customer->lifetimeOrderCount() }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-neutral-500">Lifetime Spend</p>
                                <p class="text-sm font-medium text-neutral-900">{{ number_format($customer->lifetimeSpend(), 2) }} UGX</p>
                            </div>
                        </div>

                        @if ($customer->recentOrders(3)->isNotEmpty())
                            <div class="pt-3 border-t border-neutral-100">
                                <p class="text-xs font-medium text-neutral-500 mb-2">Recent Orders</p>
                                <ul class="space-y-2">
                                    @foreach ($customer->recentOrders(3) as $recent)
                                        <li class="flex items-center justify-between text-sm">
                                            <span class="text-neutral-700">{{ $recent->invoice_number }}</span>
                                            <span class="inline-flex">
                                                <x-filament::badge size="sm" :color="OrderStatus::tryFrom($recent->status)?->color() ?? 'gray'">
                                                    {{ OrderStatus::tryFrom($recent->status)?->label() ?? $recent->status }}
                                                </x-filament::badge>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="pt-2">
                            <x-filament::button
                                tag="a"
                                :href="route('filament.admin.resources.customers.view', $customer)"
                                size="sm"
                                color="gray"
                                outlined
                            >
                                View Customer Profile
                            </x-filament::button>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-neutral-500">Customer information unavailable.</p>
                @endif
            </x-filament::section>

            {{-- Shipping & Billing Address --}}
            <div class="space-y-6 lg:col-span-2">
                <x-filament::section icon="heroicon-o-map-pin" heading="Shipping Address">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <p class="text-xs text-neutral-500">Recipient</p>
                            <p class="text-sm font-medium text-neutral-900">{{ $shipping['full_name'] ?? '-' }}</p>
                            <p class="text-sm text-neutral-600">{{ $shipping['phone'] ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-neutral-500">Address</p>
                            <p class="text-sm text-neutral-800">
                                {{ $shipping['address_line'] ?? '' }}<br>
                                {{ ($shipping['city'] ?? '') . ($shipping['region'] ? ', ' . $shipping['region'] : '') }}
                            </p>
                        </div>
                    </div>
                </x-filament::section>

                <x-filament::section icon="heroicon-o-credit-card" heading="Billing Address">
                    <p class="text-sm text-neutral-600">
                        Billing address uses the same information as shipping. Separate billing address management will be available in a future release.
                    </p>
                </x-filament::section>
            </div>
        </div>

        {{-- Ordered Items --}}
        <x-filament::section icon="heroicon-o-shopping-bag" heading="Ordered Items">
            <div class="overflow-x-auto">
                <table class="fi-ta-table w-full text-left text-sm">
                    <thead class="bg-neutral-50 text-neutral-700">
                        <tr>
                            <th class="px-4 py-2 font-medium">Product</th>
                            <th class="px-4 py-2 font-medium">SKU</th>
                            <th class="px-4 py-2 font-medium text-right">Unit Price</th>
                            <th class="px-4 py-2 font-medium text-center">Qty</th>
                            <th class="px-4 py-2 font-medium text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse ($order->items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if ($item->product && $item->product->images->first())
                                            <img src="{{ asset('storage/' . $item->product->images->first()->image) }}" alt="" class="h-10 w-10 rounded-md object-cover" onerror="this.style.display='none'">
                                        @else
                                            <div class="h-10 w-10 rounded-md bg-neutral-100 flex items-center justify-center">
                                                <x-filament::icon icon="heroicon-o-photo" class="h-5 w-5 text-neutral-400" />
                                            </div>
                                        @endif
                                        <span class="font-medium text-neutral-900">{{ $item->product_name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-mono text-neutral-600">{{ $item->product_sku }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item->unit_price, 2) }} UGX</td>
                                <td class="px-4 py-3 text-center">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right font-medium">{{ number_format($item->line_total, 2) }} UGX</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-neutral-500">No items found for this order.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Payment Information --}}
            <x-filament::section icon="heroicon-o-banknotes" heading="Payment Information" class="lg:col-span-1">
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Payment Method</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $order->paymentMethodLabel() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Payment Status</dt>
                        <dd>
                            <x-filament::badge size="sm" :color="PaymentStatus::tryFrom($order->payment_status)?->color() ?? 'gray'">
                                {{ PaymentStatus::tryFrom($order->payment_status)?->label() ?? $order->payment_status }}
                            </x-filament::badge>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Amount Paid</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ number_format($order->amountPaid(), 2) }} UGX</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Outstanding</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ number_format($order->outstandingBalance(), 2) }} UGX</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Transaction Reference</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $transaction?->transaction_reference ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Payment Date</dt>
                        <dd class="text-sm text-neutral-900">{{ $transaction?->paid_at?->format('M d, Y H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            {{-- Shipping / Fulfilment --}}
            <x-filament::section icon="heroicon-o-truck" heading="Shipping & Fulfilment" class="lg:col-span-1">
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Recipient</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $shipping['full_name'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Phone</dt>
                        <dd class="text-sm text-neutral-900">{{ $shipping['phone'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Courier</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $order->courier ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Tracking Number</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $order->tracking_number ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Dispatched At</dt>
                        <dd class="text-sm text-neutral-900">{{ $order->dispatched_at?->format('M d, Y H:i') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Delivered At</dt>
                        <dd class="text-sm text-neutral-900">{{ $order->delivered_at?->format('M d, Y H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            {{-- Internal Notes --}}
            <x-filament::section icon="heroicon-o-chat-bubble-left-ellipsis" heading="Internal Notes" class="lg:col-span-1">
                @if ($order->internal_notes)
                    <p class="text-sm text-neutral-800 whitespace-pre-line">{{ $order->internal_notes }}</p>
                @else
                    <p class="text-sm text-neutral-500">No internal notes recorded.</p>
                @endif
                <div class="mt-4">
                    <x-filament::button
                        tag="a"
                        :href="route('filament.admin.resources.orders.edit', $order)"
                        size="sm"
                        color="gray"
                        outlined
                    >
                        Edit Notes / Fulfilment
                    </x-filament::button>
                </div>
            </x-filament::section>
        </div>

        {{-- Order Timeline --}}
        <x-filament::section icon="heroicon-o-clock" heading="Order Timeline">
            <x-filament.vestra.vestra-timeline :events="$timeline" />
        </x-filament::section>

        {{-- Audit History --}}
        <x-filament::section icon="heroicon-o-shield-check" heading="Audit History">
            @php
                $auditLogs = \App\Models\AuditLog::query()
                    ->where('subject_type', $order->getMorphClass())
                    ->where('subject_id', $order->id)
                    ->with('user')
                    ->latest()
                    ->limit(20)
                    ->get();
            @endphp

            @if ($auditLogs->isNotEmpty())
                <ul class="divide-y divide-neutral-100">
                    @foreach ($auditLogs as $log)
                        <li class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                            <div>
                                <p class="text-sm text-neutral-800">
                                    <span class="font-medium">{{ $log->user?->name ?? 'System' }}</span>
                                    <span class="text-neutral-500">{{ str_replace(['.', '_'], ' ', $log->action) }}</span>
                                </p>
                                @if ($log->details)
                                    <p class="text-xs text-neutral-500">{{ json_encode($log->details) }}</p>
                                @endif
                            </div>
                            <span class="text-xs text-neutral-400">{{ $log->created_at?->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-neutral-500">No audit history available.</p>
            @endif
        </x-filament::section>

    </div>
</x-filament-panels::page>
