@php
$user = filament()->auth()->user();

$rangeOptions = [
    'this-week' => 'This Week',
    'this-month' => 'This Month',
    'last-30-days' => 'Last 30 Days',
];
@endphp

<header class="vestra-header">
    <div class="vestra-header__left">
        <button
            type="button"
            class="vestra-header__menu-btn lg:hidden"
            @click="$dispatch('toggle-mobile-sidebar')"
            aria-label="Open sidebar"
        >
            <x-filament::icon icon="heroicon-o-bars-3" class="h-6 w-6" />
        </button>

        <button
            type="button"
            class="vestra-header__collapse-btn hidden lg:flex"
            @click="$dispatch('toggle-sidebar-collapse')"
            aria-label="Toggle sidebar"
            x-data
        >
            <x-filament::icon icon="heroicon-o-chevron-double-left" class="h-5 w-5 collapse-icon" />
        </button>
    </div>

    <div class="vestra-header__search">
        <span class="vestra-header__search-icon">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-5 w-5" />
        </span>
        <input
            type="text"
            placeholder="Search customers, quotes, tickets..."
            class="vestra-header__search-input"
            aria-label="Global search"
        />
    </div>

    <div class="vestra-header__actions">
        {{-- Date selector --}}
        <div
            class="vestra-header__date"
            x-data="{ open: false, range: '{{ $dateRange }}' }"
            @click.outside="open = false"
        >
            <button
                type="button"
                @click="open = !open"
                class="vestra-header__date-trigger"
                aria-haspopup="listbox"
                :aria-expanded="open"
            >
                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                <span class="vestra-header__date-label" x-text="{
                    'this-week': 'This Week',
                    'this-month': 'This Month',
                    'last-30-days': 'Last 30 Days'
                }[range]"></span>
                <x-filament::icon
                    icon="heroicon-m-chevron-down"
                    class="h-4 w-4 vestra-header__date-chevron"
                    x-bind:class="{ 'vestra-header__date-chevron--open': open }"
                />
            </button>

            <div
                x-show="open"
                x-transition.origin.top.right
                class="vestra-header__date-dropdown"
                role="listbox"
            >
                @foreach ($rangeOptions as $value => $label)
                    <button
                        type="button"
                        @click="range = '{{ $value }}'; open = false; $dispatch('dashboard-range-changed', { range: '{{ $value }}' })"
                        class="vestra-header__date-option @if ($dateRange === $value) vestra-header__date-option--active @endif"
                        role="option"
                        aria-selected="{{ $dateRange === $value ? 'true' : 'false' }}"
                    >
                        <span>{{ $label }}</span>
                        @if ($dateRange === $value)
                            <x-filament::icon icon="heroicon-m-check" class="h-4 w-4" />
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

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
