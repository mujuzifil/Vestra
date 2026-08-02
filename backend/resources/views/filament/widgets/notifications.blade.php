@php
$notifications = $this->getNotifications();
$unreadCount = $this->getUnreadCount();
@endphp

<x-filament-widgets::widget class="fi-wi-notifications vestra-card">
    <div class="vestra-card-header">
        <div class="flex items-center gap-2">
            <h3 class="vestra-card-title">Notifications</h3>
            @if ($unreadCount > 0)
                <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-[var(--danger-500)] px-1.5 text-[10px] font-bold text-white">
                    {{ $unreadCount }}
                </span>
            @endif
        </div>
        <a href="{{ url('/admin/notification-dashboard') }}" class="vestra-card-link">
            View all
        </a>
    </div>

    <div class="flow-root">
        <ul role="list" class="divide-y divide-[var(--border-default)]">
            @forelse ($notifications as $notification)
                <li class="py-3.5 first:pt-0 last:pb-0">
                    <div class="flex items-start gap-3.5 @if(!$notification['read']) bg-[var(--primary-50)] -mx-2 px-2 py-1.5 rounded-lg @endif">
                        <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--primary-100)] text-[var(--primary-500)]">
                            <x-filament::icon :icon="$notification['icon']" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1 pt-0.5">
                            <p class="text-sm font-semibold text-[var(--text-heading)] truncate @if(!$notification['read']) text-[var(--primary-700)] @endif">
                                {{ $notification['title'] }}
                            </p>
                            @if ($notification['body'])
                                <p class="text-xs text-[var(--text-muted)] truncate mt-0.5">
                                    {{ $notification['body'] }}
                                </p>
                            @endif
                        </div>
                        <span class="text-xs text-[var(--text-muted)] whitespace-nowrap pt-1.5">
                            {{ $notification['time'] }}
                        </span>
                    </div>
                </li>
            @empty
                <li>
                    <x-admin.empty-state
                        icon="heroicon-o-bell-slash"
                        title="No notifications"
                        description="You're all caught up."
                    />
                </li>
            @endforelse
        </ul>
    </div>
</x-filament-widgets::widget>
