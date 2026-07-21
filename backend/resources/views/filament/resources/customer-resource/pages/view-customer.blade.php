@php
    use App\Enums\OrderStatus;
    use App\Enums\PaymentStatus;

    $customer = $this->getRecord();
    $customer->load(['orders' => fn ($q) => $q->latest()->limit(10), 'addresses']);

    $timeline = [];

    $timeline[] = [
        'icon' => 'heroicon-o-user-plus',
        'color' => 'primary',
        'title' => 'Customer registered',
        'description' => 'Account created on ' . ($customer->created_at?->format('M d, Y') ?? '-') . '.',
        'time' => $customer->created_at,
        'actor' => $customer->name,
    ];

    if ($customer->email_verified_at) {
        $timeline[] = [
            'icon' => 'heroicon-o-check-badge',
            'color' => 'success',
            'title' => 'Email verified',
            'description' => 'Email address was verified.',
            'time' => $customer->email_verified_at,
            'actor' => $customer->name,
        ];
    }

    foreach ($customer->orders as $order) {
        $timeline[] = [
            'icon' => 'heroicon-o-shopping-cart',
            'color' => 'info',
            'title' => 'Order placed: ' . $order->invoice_number,
            'description' => 'Order total ' . number_format($order->total_amount, 2) . ' UGX.',
            'time' => $order->created_at,
            'actor' => $customer->name,
        ];
    }

    $auditLogs = \App\Models\AuditLog::query()
        ->where('subject_type', $customer->getMorphClass())
        ->where('subject_id', $customer->id)
        ->with('user')
        ->latest()
        ->limit(20)
        ->get();

    foreach ($auditLogs as $log) {
        $timeline[] = [
            'icon' => 'heroicon-o-shield-check',
            'color' => 'gray',
            'title' => 'Profile ' . str_replace(['.', '_'], ' ', $log->action),
            'description' => $log->details ? json_encode($log->details) : 'Account updated.',
            'time' => $log->created_at,
            'actor' => $log->user?->name ?? 'System',
        ];
    }

    usort($timeline, fn (array $a, array $b): int => ($a['time'] ?? now()) <=> ($b['time'] ?? now()));
@endphp

<x-filament-panels::page>
    <div class="fi-vestra-customer-detail space-y-6">

        {{-- Customer Summary --}}
        <x-filament::section icon="heroicon-o-user" heading="Customer Summary">
            <div class="flex flex-col gap-6 md:flex-row md:items-start">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary-100 text-2xl font-bold text-primary-700">
                    {{ $customer->initials() }}
                </div>

                <div class="flex-1">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold text-neutral-900">{{ $customer->name }}</h2>
                            <p class="text-sm text-neutral-600">{{ $customer->email }}</p>
                            @if ($customer->phone)
                                <p class="text-sm text-neutral-600">{{ $customer->phone }}</p>
                            @endif
                        </div>
                        <div class="mt-2 flex items-center gap-2 sm:mt-0">
                            <span class="inline-flex">
                                <x-filament::badge :color="$customer->customerStatusColor()">
                                    {{ $customer->customerStatusLabel() }}
                                </x-filament::badge>
                            </span>
                            @if ($customer->email_verified_at)
                                <span class="inline-flex">
                                    <x-filament::badge color="success" icon="heroicon-o-check-badge">
                                        Verified
                                    </x-filament::badge>
                                </span>
                            @else
                                <span class="inline-flex">
                                    <x-filament::badge color="warning">
                                        Unverified
                                    </x-filament::badge>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg bg-neutral-50 p-3">
                            <p class="text-xs text-neutral-500">Customer ID</p>
                            <p class="text-sm font-medium text-neutral-900">#{{ $customer->id }}</p>
                        </div>
                        <div class="rounded-lg bg-neutral-50 p-3">
                            <p class="text-xs text-neutral-500">Registered</p>
                            <p class="text-sm font-medium text-neutral-900">{{ $customer->created_at?->format('M d, Y') ?? '-' }}</p>
                        </div>
                        <div class="rounded-lg bg-neutral-50 p-3">
                            <p class="text-xs text-neutral-500">Email Verified</p>
                            <p class="text-sm font-medium text-neutral-900">{{ $customer->email_verified_at?->format('M d, Y') ?? 'No' }}</p>
                        </div>
                        <div class="rounded-lg bg-neutral-50 p-3">
                            <p class="text-xs text-neutral-500">Last Updated</p>
                            <p class="text-sm font-medium text-neutral-900">{{ $customer->updated_at?->diffForHumans() ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Contact Information --}}
            <x-filament::section icon="heroicon-o-envelope" heading="Contact Information" class="lg:col-span-1">
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Email</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $customer->email }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Phone</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $customer->phone ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Verification</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $customer->email_verified_at ? 'Verified' : 'Unverified' }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            {{-- Account Status --}}
            <x-filament::section icon="heroicon-o-shield-check" heading="Account Status" class="lg:col-span-1">
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Status</dt>
                        <dd>
                            <span class="inline-flex">
                                <x-filament::badge :color="$customer->customerStatusColor()">
                                    {{ $customer->customerStatusLabel() }}
                                </x-filament::badge>
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Registration Date</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $customer->created_at?->format('M d, Y H:i') ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Admin User</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $customer->isAdmin() ? 'Yes' : 'No' }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            {{-- Quick Actions --}}
            <x-filament::section icon="heroicon-o-bolt" heading="Quick Actions" class="lg:col-span-1">
                <div class="flex flex-wrap gap-2">
                    <x-filament::button
                        tag="a"
                        :href="route('filament.admin.resources.customers.edit', $customer)"
                        size="sm"
                        icon="heroicon-o-pencil"
                    >
                        Edit Customer
                    </x-filament::button>
                    <x-filament::button
                        tag="a"
                        :href="route('filament.admin.resources.orders.index', ['tableFilters[search][customer]' => $customer->name])"
                        size="sm"
                        color="gray"
                        outlined
                    >
                        View Orders
                    </x-filament::button>
                </div>
            </x-filament::section>
        </div>

        {{-- Lifetime Statistics --}}
        <x-filament::section icon="heroicon-o-chart-bar" heading="Commerce Insights">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-primary-50 p-4">
                    <p class="text-xs text-primary-600">Lifetime Spend</p>
                    <p class="mt-1 text-lg font-semibold text-primary-700">{{ number_format($customer->lifetimeSpend(), 2) }} UGX</p>
                </div>
                <div class="rounded-lg bg-neutral-50 p-4">
                    <p class="text-xs text-neutral-500">Lifetime Orders</p>
                    <p class="mt-1 text-lg font-semibold text-neutral-900">{{ $customer->lifetimeOrderCount() }}</p>
                </div>
                <div class="rounded-lg bg-neutral-50 p-4">
                    <p class="text-xs text-neutral-500">Average Order Value</p>
                    <p class="mt-1 text-lg font-semibold text-neutral-900">{{ number_format($customer->averageOrderValue(), 2) }} UGX</p>
                </div>
                <div class="rounded-lg bg-neutral-50 p-4">
                    <p class="text-xs text-neutral-500">Largest Order</p>
                    <p class="mt-1 text-lg font-semibold text-neutral-900">{{ $customer->largestOrder() ? number_format($customer->largestOrder()->total_amount, 2) . ' UGX' : '—' }}</p>
                </div>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-neutral-50 p-4">
                    <p class="text-xs text-neutral-500">Last Order</p>
                    <p class="mt-1 text-sm font-semibold text-neutral-900">{{ $customer->lastOrderAt()?->diffForHumans() ?? 'Never ordered' }}</p>
                </div>
                <div class="rounded-lg bg-neutral-50 p-4">
                    <p class="text-xs text-neutral-500">Favourite Category</p>
                    <p class="mt-1 text-sm font-semibold text-neutral-900">{{ $customer->favouriteCategory() ?? '—' }}</p>
                </div>
                <div class="rounded-lg bg-neutral-50 p-4">
                    <p class="text-xs text-neutral-500">Favourite Product</p>
                    <p class="mt-1 text-sm font-semibold text-neutral-900">{{ $customer->favouriteProduct() ?? '—' }}</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Recent Orders --}}
        <x-filament::section icon="heroicon-o-shopping-bag" heading="Recent Orders">
            <div class="overflow-x-auto">
                <table class="fi-ta-table w-full text-left text-sm">
                    <thead class="bg-neutral-50 text-neutral-700">
                        <tr>
                            <th class="px-4 py-2 font-medium">Invoice</th>
                            <th class="px-4 py-2 font-medium">Date</th>
                            <th class="px-4 py-2 font-medium">Status</th>
                            <th class="px-4 py-2 font-medium">Payment</th>
                            <th class="px-4 py-2 font-medium text-right">Total</th>
                            <th class="px-4 py-2 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse ($customer->orders->take(5) as $order)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-neutral-900">{{ $order->invoice_number }}</td>
                                <td class="px-4 py-3 text-neutral-600">{{ $order->created_at?->format('M d, Y') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex">
                                        <x-filament::badge size="sm" :color="OrderStatus::tryFrom($order->status)?->color() ?? 'gray'">
                                            {{ OrderStatus::tryFrom($order->status)?->label() ?? $order->status }}
                                        </x-filament::badge>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex">
                                        <x-filament::badge size="sm" :color="PaymentStatus::tryFrom($order->payment_status)?->color() ?? 'gray'">
                                            {{ PaymentStatus::tryFrom($order->payment_status)?->label() ?? $order->payment_status }}
                                        </x-filament::badge>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium">{{ number_format($order->total_amount, 2) }} UGX</td>
                                <td class="px-4 py-3 text-right">
                                    <x-filament::button
                                        tag="a"
                                        :href="route('filament.admin.resources.orders.view', $order)"
                                        size="xs"
                                        color="gray"
                                        outlined
                                    >
                                        View
                                    </x-filament::button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-neutral-500">No orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Saved Addresses --}}
        <x-filament::section icon="heroicon-o-map-pin" heading="Saved Addresses">
            @if ($customer->addresses->isNotEmpty())
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($customer->addresses as $address)
                        <div class="rounded-lg border border-neutral-200 bg-white p-4">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="inline-flex">
                                    <x-filament::badge size="sm" :color="$address->is_default ? 'primary' : 'gray'">
                                        {{ $address->label }}
                                    </x-filament::badge>
                                </span>
                                @if ($address->is_default)
                                    <span class="text-xs text-neutral-500">Default</span>
                                @endif
                            </div>
                            <p class="font-medium text-neutral-900">{{ $address->full_name }}</p>
                            <p class="text-sm text-neutral-600">{{ $address->phone }}</p>
                            <p class="mt-2 text-sm text-neutral-700">{{ $address->address_line }}</p>
                            <p class="text-sm text-neutral-700">
                                {{ $address->city }}{{ $address->region ? ', ' . $address->region : '' }}{{ $address->district ? ' — ' . $address->district : '' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-lg border border-dashed border-neutral-200 bg-neutral-50 p-6 text-center">
                    <x-filament::icon icon="heroicon-o-map-pin" class="mx-auto h-8 w-8 text-neutral-300" />
                    <p class="mt-2 text-sm text-neutral-500">No saved addresses.</p>
                </div>
            @endif
        </x-filament::section>

        {{-- Activity Timeline --}}
        <x-filament::section icon="heroicon-o-clock" heading="Activity Timeline">
            <x-filament.vestra.vestra-timeline :events="$timeline" />
        </x-filament::section>

        {{-- Notes --}}
        <x-filament::section icon="heroicon-o-chat-bubble-left-ellipsis" heading="Customer Notes">
            <div class="rounded-lg border border-dashed border-neutral-200 bg-neutral-50 p-6 text-center">
                <x-filament::icon icon="heroicon-o-document-text" class="mx-auto h-8 w-8 text-neutral-300" />
                <p class="mt-2 text-sm text-neutral-500">Customer notes will be available in a future CRM release.</p>
            </div>
        </x-filament::section>

        {{-- Audit History --}}
        <x-filament::section icon="heroicon-o-shield-check" heading="Audit History">
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
