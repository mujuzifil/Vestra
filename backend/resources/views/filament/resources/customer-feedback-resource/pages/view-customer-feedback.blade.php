@php
    use App\Enums\FeedbackCategory;
    use App\Enums\FeedbackStatus;
    use App\Enums\Priority;

    $feedback = $this->getRecord();
    $feedback->load('user');

    $timeline = [];

    $timeline[] = [
        'icon' => 'heroicon-o-chat-bubble-left-right',
        'color' => 'primary',
        'title' => 'Feedback submitted',
        'description' => 'A ' . strtolower($feedback->categoryLabel()) . ' was submitted: ' . $feedback->subject . '.',
        'time' => $feedback->created_at,
        'actor' => $feedback->user?->name ?? 'Customer',
    ];

    if ($feedback->status !== FeedbackStatus::NEW->value) {
        $timeline[] = [
            'icon' => 'heroicon-o-arrow-path',
            'color' => $feedback->statusColor(),
            'title' => 'Status changed to ' . $feedback->statusLabel(),
            'description' => 'Feedback status was updated.',
            'time' => $feedback->updated_at,
            'actor' => 'Administrator',
        ];
    }

    if ($feedback->isRead()) {
        $timeline[] = [
            'icon' => 'heroicon-o-envelope-open',
            'color' => 'success',
            'title' => 'Marked as read',
            'description' => 'An administrator read the feedback.',
            'time' => $feedback->read_at,
            'actor' => 'Administrator',
        ];
    }

    $auditLogs = \App\Models\AuditLog::query()
        ->where('subject_type', $feedback->getMorphClass())
        ->where('subject_id', $feedback->id)
        ->with('user')
        ->latest()
        ->limit(20)
        ->get();

    foreach ($auditLogs as $log) {
        $timeline[] = [
            'icon' => 'heroicon-o-pencil-square',
            'color' => 'gray',
            'title' => 'Feedback ' . str_replace(['.', '_'], ' ', $log->action),
            'description' => $log->details ? json_encode($log->details) : 'Feedback updated.',
            'time' => $log->created_at,
            'actor' => $log->user?->name ?? 'System',
        ];
    }

    usort($timeline, fn (array $a, array $b): int => ($a['time'] ?? now()) <=> ($b['time'] ?? now()));
@endphp

<x-filament-panels::page>
    <div class="fi-vestra-engagement-detail space-y-6">

        {{-- Feedback Header --}}
        <x-filament::section icon="heroicon-o-chat-bubble-left-right" heading="Feedback Summary">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-neutral-900">{{ $feedback->subject }}</h2>
                    <p class="text-sm text-neutral-600">Submitted {{ $feedback->created_at?->diffForHumans() ?? '-' }}</p>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-2 sm:mt-0">
                    <x-filament::badge size="lg" :color="$feedback->statusColor()">
                        {{ $feedback->statusLabel() }}
                    </x-filament::badge>
                    <x-filament::badge size="lg" :color="$feedback->priorityColor()">
                        {{ $feedback->priorityLabel() }}
                    </x-filament::badge>
                    @if (! $feedback->isRead())
                        <x-filament::badge size="lg" color="warning">Unread</x-filament::badge>
                    @endif
                </div>
            </div>

            <div class="mt-6 rounded-lg bg-neutral-50 p-4">
                <p class="text-base text-neutral-800">{{ $feedback->message }}</p>
            </div>
        </x-filament::section>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Customer Summary --}}
            <x-filament::section icon="heroicon-o-user" heading="Customer" class="lg:col-span-1">
                @if ($feedback->user)
                    <div class="flex items-center gap-3">
                        <div class="fi-vestra-avatar h-12 w-12 text-sm">
                            {{ $feedback->user->initials() }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-neutral-900">{{ $feedback->user->name }}</p>
                            <p class="text-xs text-neutral-500">{{ $feedback->user->email }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            :href="route('filament.admin.resources.customers.view', $feedback->user)"
                            size="sm"
                            color="gray"
                            outlined
                            class="w-full"
                        >
                            View Customer
                        </x-filament::button>
                    </div>
                @else
                    <p class="text-sm text-neutral-500">Guest submission.</p>
                @endif
            </x-filament::section>

            {{-- Categorisation --}}
            <x-filament::section icon="heroicon-o-tag" heading="Categorisation" class="lg:col-span-1">
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Category</dt>
                        <dd>
                            <x-filament::badge color="info">
                                {{ FeedbackCategory::tryFrom($feedback->category)?->label() ?? ucfirst($feedback->category) }}
                            </x-filament::badge>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Status</dt>
                        <dd>
                            <x-filament::badge :color="$feedback->statusColor()">{{ $feedback->statusLabel() }}</x-filament::badge>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Priority</dt>
                        <dd>
                            <x-filament::badge :color="$feedback->priorityColor()">{{ $feedback->priorityLabel() }}</x-filament::badge>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Read</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $feedback->isRead() ? 'Yes' : 'No' }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            {{-- Assignment Placeholder --}}
            <x-filament::section icon="heroicon-o-user-plus" heading="Assignment" class="lg:col-span-1">
                <div class="rounded-lg border border-dashed border-neutral-200 bg-neutral-50 p-4 text-center">
                    <x-filament::icon icon="heroicon-o-user-plus" class="mx-auto h-8 w-8 text-neutral-300" />
                    <p class="mt-2 text-sm text-neutral-500">Administrator assignment will be available in a future release.</p>
                </div>
            </x-filament::section>
        </div>

        {{-- Internal Notes Placeholder --}}
        <x-filament::section icon="heroicon-o-document-text" heading="Internal Notes">
            <div class="rounded-lg border border-dashed border-neutral-200 bg-neutral-50 p-6 text-center">
                <x-filament::icon icon="heroicon-o-document-text" class="mx-auto h-8 w-8 text-neutral-300" />
                <p class="mt-2 text-sm text-neutral-500">Internal notes will be available in a future CRM release.</p>
            </div>
        </x-filament::section>

        {{-- Response History Placeholder --}}
        <x-filament::section icon="heroicon-o-arrow-uturn-left" heading="Response History">
            <div class="rounded-lg border border-dashed border-neutral-200 bg-neutral-50 p-6 text-center">
                <x-filament::icon icon="heroicon-o-arrow-uturn-left" class="mx-auto h-8 w-8 text-neutral-300" />
                <p class="mt-2 text-sm text-neutral-500">Customer response history will be available when messaging integration is completed.</p>
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
