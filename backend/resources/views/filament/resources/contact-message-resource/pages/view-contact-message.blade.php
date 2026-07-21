@php
    use App\Enums\ContactStatus;
    use App\Enums\Priority;

    $message = $this->getRecord();

    $timeline = [];

    $timeline[] = [
        'icon' => 'heroicon-o-envelope',
        'color' => 'primary',
        'title' => 'Message received',
        'description' => 'Contact message regarding "' . $message->subject . '" was received.',
        'time' => $message->created_at,
        'actor' => $message->name,
    ];

    if ($message->status !== ContactStatus::NEW->value) {
        $timeline[] = [
            'icon' => 'heroicon-o-arrow-path',
            'color' => $message->statusColor(),
            'title' => 'Status changed to ' . $message->statusLabel(),
            'description' => 'Message status was updated.',
            'time' => $message->updated_at,
            'actor' => 'Administrator',
        ];
    }

    if ($message->isRead()) {
        $timeline[] = [
            'icon' => 'heroicon-o-envelope-open',
            'color' => 'success',
            'title' => 'Marked as read',
            'description' => 'An administrator read the message.',
            'time' => $message->read_at,
            'actor' => 'Administrator',
        ];
    }

    if ($message->isReplied()) {
        $timeline[] = [
            'icon' => 'heroicon-o-paper-airplane',
            'color' => 'success',
            'title' => 'Reply sent',
            'description' => 'A reply was emailed to ' . $message->email . '.',
            'time' => $message->replied_at,
            'actor' => 'Administrator',
        ];
    }

    $auditLogs = \App\Models\AuditLog::query()
        ->where('subject_type', $message->getMorphClass())
        ->where('subject_id', $message->id)
        ->with('user')
        ->latest()
        ->limit(20)
        ->get();

    foreach ($auditLogs as $log) {
        $timeline[] = [
            'icon' => 'heroicon-o-pencil-square',
            'color' => 'gray',
            'title' => 'Message ' . str_replace(['.', '_'], ' ', $log->action),
            'description' => $log->details ? json_encode($log->details) : 'Message updated.',
            'time' => $log->created_at,
            'actor' => $log->user?->name ?? 'System',
        ];
    }

    usort($timeline, fn (array $a, array $b): int => ($a['time'] ?? now()) <=> ($b['time'] ?? now()));
@endphp

<x-filament-panels::page>
    <div class="fi-vestra-engagement-detail space-y-6">

        {{-- Message Header --}}
        <x-filament::section icon="heroicon-o-envelope" heading="Message Summary">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-neutral-900">{{ $message->subject }}</h2>
                    <p class="text-sm text-neutral-600">Received {{ $message->created_at?->diffForHumans() ?? '-' }}</p>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-2 sm:mt-0">
                    <x-filament::badge size="lg" :color="$message->statusColor()">
                        {{ $message->statusLabel() }}
                    </x-filament::badge>
                    <x-filament::badge size="lg" :color="$message->priorityColor()">
                        {{ $message->priorityLabel() }}
                    </x-filament::badge>
                    @if (! $message->isRead())
                        <x-filament::badge size="lg" color="warning">Unread</x-filament::badge>
                    @endif
                    @if ($message->isReplied())
                        <x-filament::badge size="lg" color="success">Replied</x-filament::badge>
                    @endif
                </div>
            </div>

            <div class="mt-6 rounded-lg bg-neutral-50 p-4">
                <p class="text-base text-neutral-800">{{ $message->message }}</p>
            </div>
        </x-filament::section>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Sender Information --}}
            <x-filament::section icon="heroicon-o-user" heading="Sender" class="lg:col-span-1">
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Name</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $message->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Email</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $message->email }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Phone</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $message->phone ?? '—' }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            {{-- Conversation Metadata --}}
            <x-filament::section icon="heroicon-o-information-circle" heading="Metadata" class="lg:col-span-1">
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Status</dt>
                        <dd>
                            <x-filament::badge :color="$message->statusColor()">{{ $message->statusLabel() }}</x-filament::badge>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Priority</dt>
                        <dd>
                            <x-filament::badge :color="$message->priorityColor()">{{ $message->priorityLabel() }}</x-filament::badge>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Read</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $message->isRead() ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Replied</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $message->isReplied() ? 'Yes' : 'No' }}</dd>
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

        {{-- Attachments Placeholder --}}
        <x-filament::section icon="heroicon-o-paper-clip" heading="Attachments">
            <div class="rounded-lg border border-dashed border-neutral-200 bg-neutral-50 p-6 text-center">
                <x-filament::icon icon="heroicon-o-paper-clip" class="mx-auto h-8 w-8 text-neutral-300" />
                <p class="mt-2 text-sm text-neutral-500">Attachment support will be available in a future release.</p>
            </div>
        </x-filament::section>

        {{-- Reply Placeholder --}}
        <x-filament::section icon="heroicon-o-arrow-uturn-left" heading="Reply">
            @if ($message->reply)
                <div class="rounded-lg bg-neutral-50 p-4">
                    <p class="text-sm text-neutral-600">Saved reply:</p>
                    <p class="mt-1 text-base text-neutral-800">{{ $message->reply }}</p>
                    @if ($message->isReplied())
                        <p class="mt-2 text-xs text-success-600">This reply was emailed to the customer on {{ $message->replied_at?->format('M d, Y H:i') }}.</p>
                    @else
                        <p class="mt-2 text-xs text-neutral-500">This reply has not been sent yet. Use the Edit page to send it.</p>
                    @endif
                </div>
            @else
                <div class="rounded-lg border border-dashed border-neutral-200 bg-neutral-50 p-6 text-center">
                    <x-filament::icon icon="heroicon-o-paper-airplane" class="mx-auto h-8 w-8 text-neutral-300" />
                    <p class="mt-2 text-sm text-neutral-500">No reply drafted. Use the Edit page to compose and send a reply.</p>
                </div>
            @endif
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
