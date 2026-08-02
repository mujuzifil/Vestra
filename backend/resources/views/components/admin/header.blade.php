@php
$user = filament()->auth()->user();
$unreadCount = $user?->unreadNotifications()->count() ?? 0;
@endphp

<header class="vestra-header">
    <button
        type="button"
        class="vestra-header__menu-btn lg:hidden"
        @click="$dispatch('toggle-mobile-sidebar')"
        aria-label="Open sidebar"
    >
        <x-filament::icon icon="heroicon-o-bars-3" class="h-6 w-6" />
    </button>

    <div class="vestra-header__search">
        <span class="vestra-header__search-icon">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-5 w-5" />
        </span>
        <input
            type="text"
            placeholder="Search anything..."
            class="vestra-header__search-input"
            aria-label="Global search"
        />
        <span class="vestra-header__search-kbd">⌘ K</span>
    </div>

    <div class="vestra-header__actions">
        {{-- Date selector --}}
        <div class="vestra-header__date">
            <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
            <select
                wire:change="$dispatch('dashboard-range-changed', { range: $event.target.value })"
                aria-label="Select dashboard date range"
                class="vestra-header__date-select"
            >
                <option value="this-week" @selected($dateRange === 'this-week')>This Week</option>
                <option value="this-month" @selected($dateRange === 'this-month')>This Month</option>
                <option value="last-30-days" @selected($dateRange === 'last-30-days')>Last 30 Days</option>
            </select>
            <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" />
        </div>

        {{-- Notifications --}}
        <button type="button" class="vestra-header__action" aria-label="Notifications">
            <x-filament::icon icon="heroicon-o-bell" class="h-5 w-5" />
            @if ($unreadCount > 0)
                <span class="vestra-header__badge">{{ $unreadCount }}</span>
            @endif
        </button>

        {{-- Help --}}
        <button type="button" class="vestra-header__action" aria-label="Help">
            <x-filament::icon icon="heroicon-o-question-mark-circle" class="h-5 w-5" />
        </button>

        {{-- User menu --}}
        <div x-data="{ open: false }" class="vestra-header__user">
            <button
                type="button"
                @click="open = !open"
                class="vestra-header__user-trigger"
                aria-haspopup="true"
                :aria-expanded="open"
            >
                <span class="vestra-header__user-avatar">
                    {{ $user ? strtoupper(substr($user->name, 0, 1)) : 'A' }}
                </span>
                <span class="vestra-header__user-name hidden sm:block">{{ $user?->name ?? 'Admin' }}</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4 hidden sm:block" />
            </button>

            <div
                x-show="open"
                @click.outside="open = false"
                x-transition
                class="vestra-header__user-dropdown"
            >
                <a href="{{ url('/profile') }}" class="vestra-header__user-link">Profile</a>
                <form method="POST" action="{{ filament()->getLogoutUrl() }}" class="block">
                    @csrf
                    <button type="submit" class="vestra-header__user-link w-full text-left">
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
