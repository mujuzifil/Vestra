@php
    use App\Enums\ReviewStatus;

    $review = $this->getRecord();
    $review->load(['user', 'product']);

    $timeline = [];

    $timeline[] = [
        'icon' => 'heroicon-o-chat-bubble-bottom-center-text',
        'color' => 'primary',
        'title' => 'Review submitted',
        'description' => 'A ' . $review->rating . '-star review was submitted for ' . ($review->product?->name ?? 'a product') . '.',
        'time' => $review->created_at,
        'actor' => $review->user?->name ?? 'Customer',
    ];

    if ($review->status !== ReviewStatus::PENDING->value) {
        $timeline[] = [
            'icon' => 'heroicon-o-shield-check',
            'color' => $review->statusColor(),
            'title' => 'Review ' . $review->statusLabel(),
            'description' => 'Moderation status changed to ' . $review->statusLabel() . '.',
            'time' => $review->updated_at,
            'actor' => 'Administrator',
        ];
    }

    if ($review->is_hidden) {
        $timeline[] = [
            'icon' => 'heroicon-o-eye-slash',
            'color' => 'danger',
            'title' => 'Review hidden',
            'description' => 'The review was hidden from public display.',
            'time' => $review->updated_at,
            'actor' => 'Administrator',
        ];
    }

    $auditLogs = \App\Models\AuditLog::query()
        ->where('subject_type', $review->getMorphClass())
        ->where('subject_id', $review->id)
        ->with('user')
        ->latest()
        ->limit(20)
        ->get();

    foreach ($auditLogs as $log) {
        $timeline[] = [
            'icon' => 'heroicon-o-pencil-square',
            'color' => 'gray',
            'title' => 'Review ' . str_replace(['.', '_'], ' ', $log->action),
            'description' => $log->details ? json_encode($log->details) : 'Review updated.',
            'time' => $log->created_at,
            'actor' => $log->user?->name ?? 'System',
        ];
    }

    usort($timeline, fn (array $a, array $b): int => ($a['time'] ?? now()) <=> ($b['time'] ?? now()));
@endphp

<x-filament-panels::page>
    <div class="fi-vestra-engagement-detail space-y-6">

        {{-- Review Header / Summary --}}
        <x-filament::section icon="heroicon-o-star" heading="Review Summary">
            <div class="flex flex-col gap-6 md:flex-row md:items-start">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary-100 text-3xl font-bold text-primary-700">
                    {{ $review->rating }}
                </div>

                <div class="flex-1">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold text-neutral-900">{{ $review->title ?? 'Untitled Review' }}</h2>
                            <p class="text-sm text-neutral-600">Submitted {{ $review->created_at?->diffForHumans() ?? '-' }}</p>
                        </div>
                        <div class="mt-2 flex flex-wrap items-center gap-2 sm:mt-0">
                            <x-filament::badge size="lg" :color="$review->statusColor()">
                                {{ $review->statusLabel() }}
                            </x-filament::badge>
                            <x-filament::badge size="lg" :color="$review->moderationStatusColor()">
                                {{ $review->moderationStatusLabel() }}
                            </x-filament::badge>
                        </div>
                    </div>

                    <p class="mt-4 text-base text-neutral-800">{{ $review->comment ?? 'No comment provided.' }}</p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <x-filament::button
                            tag="a"
                            :href="route('filament.admin.resources.products.edit', $review->product)"
                            size="sm"
                            color="gray"
                            outlined
                            icon="heroicon-o-shopping-bag"
                        >
                            View Product
                        </x-filament::button>
                        @if ($review->user)
                            <x-filament::button
                                tag="a"
                                :href="route('filament.admin.resources.customers.view', $review->user)"
                                size="sm"
                                color="gray"
                                outlined
                                icon="heroicon-o-user"
                            >
                                View Customer
                            </x-filament::button>
                        @endif
                    </div>
                </div>
            </div>
        </x-filament::section>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Product Summary --}}
            <x-filament::section icon="heroicon-o-shopping-bag" heading="Product Summary" class="lg:col-span-1">
                <div class="flex items-center gap-3">
                    <div class="fi-vestra-avatar h-12 w-12 text-sm">
                        {{ $review->product?->name ? strtoupper(substr($review->product->name, 0, 1)) : '?' }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-neutral-900">{{ $review->product?->name ?? 'Unknown Product' }}</p>
                        <p class="text-xs text-neutral-500">SKU: {{ $review->product?->sku ?? '—' }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <x-filament::button
                        tag="a"
                        :href="route('filament.admin.resources.products.edit', $review->product)"
                        size="sm"
                        color="gray"
                        outlined
                        class="w-full"
                    >
                        Edit Product
                    </x-filament::button>
                </div>
            </x-filament::section>

            {{-- Customer Summary --}}
            <x-filament::section icon="heroicon-o-user" heading="Customer Summary" class="lg:col-span-1">
                @if ($review->user)
                    <div class="flex items-center gap-3">
                        <div class="fi-vestra-avatar h-12 w-12 text-sm">
                            {{ $review->user->initials() }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-neutral-900">{{ $review->user->name }}</p>
                            <p class="text-xs text-neutral-500">{{ $review->user->email }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            :href="route('filament.admin.resources.customers.view', $review->user)"
                            size="sm"
                            color="gray"
                            outlined
                            class="w-full"
                        >
                            View Customer
                        </x-filament::button>
                    </div>
                @else
                    <p class="text-sm text-neutral-500">Guest reviewer.</p>
                @endif
            </x-filament::section>

            {{-- Moderation Actions --}}
            <x-filament::section icon="heroicon-o-shield-check" heading="Moderation" class="lg:col-span-1">
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Status</dt>
                        <dd>
                            <x-filament::badge :color="$review->statusColor()">{{ $review->statusLabel() }}</x-filament::badge>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Visibility</dt>
                        <dd>
                            <x-filament::badge :color="$review->moderationStatusColor()">{{ $review->moderationStatusLabel() }}</x-filament::badge>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Last Updated</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $review->updated_at?->diffForHumans() ?? '-' }}</dd>
                    </div>
                </dl>
            </x-filament::section>
        </div>

        {{-- Review Images Placeholder --}}
        <x-filament::section icon="heroicon-o-photo" heading="Review Images">
            <div class="rounded-lg border border-dashed border-neutral-200 bg-neutral-50 p-6 text-center">
                <x-filament::icon icon="heroicon-o-photo" class="mx-auto h-8 w-8 text-neutral-300" />
                <p class="mt-2 text-sm text-neutral-500">Customer-submitted review images will be available in a future release.</p>
            </div>
        </x-filament::section>

        {{-- Activity Timeline --}}
        <x-filament::section icon="heroicon-o-clock" heading="Activity Timeline">
            <x-filament.vestra.vestra-timeline :events="$timeline" />
        </x-filament::section>

        {{-- Audit History --}}
        <x-filament::section icon="heroicon-o-shield-check" heading="Audit History">
            @if ($auditLogs->isNotEmpty())
                <ul class="divide-y divide-neutral-100">
                    @foreach ($auditLogs as $log)
                        <li class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
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
