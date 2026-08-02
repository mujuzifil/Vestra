@php
$notifications = $this->getNotifications();
$unreadCount = $this->getUnreadCount();
@endphp

<x-filament-widgets::widget class="fi-wi-notifications vestra-card">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <h3 class="text-base font-semibold text-[var(--text-heading)]">Notifications</h3>
            @if ($unreadCount > 0)
                <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-[var(--danger-500)] px-1.5 text-[10px] font-bold text-white">
                    {{ $unreadCount }}
                </span>
            @endif
        </div>
        <a href="{{ url('/admin/notification-dashboard') }}" class="text-sm font-medium text-[var(--primary-500)] hover:text-[var(--primary-600)]">
            View all
        </a>
    </div>

    <div class="flow-root">
        <ul role="list" class="divide-y divide-[var(--border-default)]">
            @forelse ($notifications as $notification)
                <li class="py-3 first:pt-0 last:pb-0">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[var(--primary-100)] text-[var(--primary-500)]">
                            <x-filament::icon :icon="$notification['icon']" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-[var(--text-heading)] truncate">
                                {{ $notification['title'] }}
                            </p>
                            @if ($notification['body'])
                                <p class="text-xs text-[var(--text-muted)] truncate">
                                    {{ $notification['body'] }}
                                </p>
                            @endif
                        </div>
                        <span class="text-xs text-[var(--text-muted)] whitespace-nowrap">
                            {{ $notification['time'] }}
                        </span>
                    </div>
                </li>
            @empty
                <li class="py-8">
                    <div class="flex flex-col items-center text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--neutral-100)] text-[var(--neutral-400)]">
                            <x-filament::icon icon="heroicon-o-bell-slash" class="h-6 w-6" />
                        </span>
                        <p class="mt-3 text-sm font-medium text-[var(--text-heading)]">No notifications</p>
                        <p class="mt-1 text-xs text-[var(--text-muted)]">You're all caught up.</p>
                    </div>
                </li>
            @endforelse
        </ul>
    </div>
</x-filament-widgets::widget>
