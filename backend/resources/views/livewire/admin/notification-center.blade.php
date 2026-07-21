<div
    class="fi-notification-center relative"
    x-data="{ open: @entangle('isOpen') }"
    x-on:keydown.escape.window="open = false"
>
    <button
        type="button"
        class="fi-notification-trigger"
        x-on:click="open = ! open"
        aria-label="{{ __('Notifications') }}"
        aria-haspopup="true"
        x-bind:aria-expanded="open"
    >
        <x-filament::icon
            icon="heroicon-o-bell"
            class="h-6 w-6"
        />

        @if ($unreadCount > 0)
            <span class="fi-notification-badge">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="fi-notification-panel absolute right-0 top-full z-50 mt-2 w-80 overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-lg sm:w-96"
        x-on:click.outside="open = false"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="notification-menu-button"
        tabindex="-1"
    >
        <div class="flex items-center justify-between border-b border-neutral-200 px-4 py-3">
            <h3 class="text-sm font-semibold text-neutral-800">
                {{ __('Notifications') }}
            </h3>

            @if ($unreadCount > 0)
                <button
                    type="button"
                    wire:click="markAllRead"
                    class="text-xs font-medium text-primary-600 hover:text-primary-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-400 rounded-sm"
                >
                    {{ __('Mark all as read') }}
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse ($notifications as $notification)
                <div
                    class="flex gap-3 px-4 py-3 transition-colors hover:bg-neutral-50 {{ $notification['read'] ? 'opacity-75' : 'bg-primary-50/30' }}"
                    role="menuitem"
                >
                    <div class="shrink-0 pt-0.5">
                        <div @class([
                            'flex h-8 w-8 items-center justify-center rounded-full',
                            'bg-info-100 text-info-600' => $notification['priority'] === 'info',
                            'bg-warning-100 text-warning-600' => $notification['priority'] === 'warning',
                            'bg-success-100 text-success-600' => $notification['priority'] === 'success',
                            'bg-danger-100 text-danger-600' => $notification['priority'] === 'danger',
                        ])>
                            <x-filament::icon
                                :icon="$notification['icon']"
                                class="h-4 w-4"
                            />
                        </div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-neutral-800">
                            {{ $notification['title'] }}
                        </p>
                        <p class="text-xs text-neutral-500">
                            {{ $notification['message'] }}
                        </p>
                        <p class="mt-1 text-xs text-neutral-400">
                            {{ $notification['time'] }}
                        </p>
                    </div>

                    @if (! $notification['read'])
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary-500"></span>
                    @endif
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <x-filament::icon
                        icon="heroicon-o-bell-slash"
                        class="mx-auto h-10 w-10 text-neutral-300"
                    />
                    <p class="mt-2 text-sm text-neutral-500">
                        {{ __('No notifications yet') }}
                    </p>
                </div>
            @endforelse
        </div>

        <div class="border-t border-neutral-200 bg-neutral-50 px-4 py-2">
            <p class="text-xs text-neutral-400">
                {{ __('Notification centre foundation — backend integration pending') }}
            </p>
        </div>
    </div>
</div>
