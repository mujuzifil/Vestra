@php
$navigation = filament()->getNavigation();
$user = filament()->auth()->user();
@endphp

<aside
    x-data="{ open: true, mobileOpen: false }"
    class="vestra-sidebar"
    :class="{ 'vestra-sidebar--collapsed': !open, 'vestra-sidebar--mobile-open': mobileOpen }"
>
    <div class="vestra-sidebar__brand">
        <img src="{{ asset('images/vestra-logo.png') }}" alt="VESTRA" class="vestra-sidebar__logo" />
        <span class="vestra-sidebar__portal">Admin Portal</span>
    </div>

    <nav class="vestra-sidebar__nav" aria-label="Main">
        @foreach ($navigation as $group)
            @php
                $groupLabel = $group->getLabel();
                $groupItems = $group->getItems();
                $groupActive = $group->isActive();
                $groupCollapsed = $group->isCollapsed();
            @endphp

            <div
                x-data="{ expanded: {{ $groupCollapsed ? 'false' : 'true' }} }"
                class="vestra-sidebar__group"
            >
                @if ($groupLabel)
                    <button
                        type="button"
                        @click="expanded = !expanded"
                        class="vestra-sidebar__group-button"
                        :aria-expanded="expanded"
                    >
                        @if ($group->getIcon())
                            <x-filament::icon :icon="$group->getIcon()" class="vestra-sidebar__group-icon" />
                        @endif
                        <span class="vestra-sidebar__group-label">{{ $groupLabel }}</span>
                        <x-filament::icon
                            icon="heroicon-m-chevron-down"
                            class="vestra-sidebar__group-chevron"
                            x-bind:class="{ 'vestra-sidebar__group-chevron--rotated': expanded }"
                        />
                    </button>
                @endif

                <div
                    x-show="expanded"
                    x-collapse
                    class="vestra-sidebar__group-items"
                >
                    @foreach ($groupItems as $item)
                        @php
                            $url = $item->getUrl();
                            $active = $item->isActive();
                            $badge = $item->getBadge();
                        @endphp

                        <a
                            href="{{ $url }}"
                            @class(['vestra-sidebar__item', 'vestra-sidebar__item--active' => $active])
                            @if ($active) aria-current="page" @endif
                        >
                            <span class="vestra-sidebar__item-icon-wrap">
                                <x-filament::icon :icon="$item->getIcon()" class="vestra-sidebar__item-icon" />
                            </span>
                            <span class="vestra-sidebar__item-label">{{ $item->getLabel() }}</span>
                            @if ($badge)
                                <span class="vestra-sidebar__item-badge">{{ $badge }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="vestra-sidebar__footer">
        <div class="vestra-sidebar__user">
            <span class="vestra-sidebar__user-avatar">
                {{ $user ? strtoupper(substr($user->name, 0, 1)) : 'A' }}
            </span>
            <div class="vestra-sidebar__user-info">
                <span class="vestra-sidebar__user-name">{{ $user?->name ?? 'Admin User' }}</span>
                <span class="vestra-sidebar__user-role">{{ $user?->roles?->first()?->name ?? 'Administrator' }}</span>
            </div>
        </div>
    </div>
</aside>
