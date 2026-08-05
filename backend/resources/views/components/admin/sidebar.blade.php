@php
$navigation = filament()->getNavigation();
$user = filament()->auth()->user();
@endphp

<aside
    x-data="{
        open: true,
        mobileOpen: false,
        collapsed: JSON.parse(localStorage.getItem('vestra-sidebar-collapsed') || 'false'),
    }"
    @toggle-mobile-sidebar.window="mobileOpen = !mobileOpen"
    @toggle-sidebar-collapse.window="if (window.innerWidth >= 1024) collapsed = !collapsed"
    class="vestra-sidebar"
    :class="{
        'vestra-sidebar--collapsed': collapsed,
        'vestra-sidebar--mobile-open': mobileOpen
    }"
    x-cloak
>
    <div class="vestra-sidebar__brand">
        <div class="vestra-sidebar__brand-lockup">
            <img
                src="{{ asset('images/vestra-logo.png') }}"
                alt="VESTRA"
                class="vestra-sidebar__logo"
                width="857"
                height="474"
                decoding="async"
            />
            <span class="vestra-sidebar__portal">Admin Portal</span>
        </div>
        <img
            src="{{ asset('images/vestra-logo.png') }}"
            alt="VESTRA"
            class="vestra-sidebar__logo-mark"
            width="857"
            height="474"
            decoding="async"
        />
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
                            $label = $item->getLabel();
                        @endphp

                        <a
                            href="{{ $url }}"
                            @class(['vestra-sidebar__item', 'vestra-sidebar__item--active' => $active])
                            @if ($active) aria-current="page" @endif
                            x-data="{ tooltip: false }"
                            @mouseenter="if (collapsed) tooltip = true"
                            @mouseleave="tooltip = false"
                        >
                            <span class="vestra-sidebar__item-icon-wrap">
                                <x-filament::icon :icon="$item->getIcon()" class="vestra-sidebar__item-icon" />
                            </span>
                            <span class="vestra-sidebar__item-label">{{ $label }}</span>
                            @if ($badge)
                                <span class="vestra-sidebar__item-badge">{{ $badge }}</span>
                            @endif

                            <span
                                x-show="tooltip"
                                x-transition.opacity
                                class="vestra-sidebar__item-tooltip"
                                role="tooltip"
                            >{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="vestra-sidebar__footer">
        <button
            type="button"
            class="vestra-sidebar__footer-btn vestra-sidebar__collapse-btn"
            @click="$dispatch('toggle-sidebar-collapse')"
            :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            x-data="{ tooltip: false }"
            @mouseenter="if (collapsed) tooltip = true"
            @mouseleave="tooltip = false"
        >
            <x-filament::icon
                icon="heroicon-o-chevron-double-left"
                class="h-5 w-5 vestra-sidebar__collapse-icon"
            />
            <span class="vestra-sidebar__footer-label" x-text="collapsed ? 'Expand' : 'Collapse'"></span>
            <span
                x-show="tooltip"
                x-transition.opacity
                class="vestra-sidebar__item-tooltip"
                role="tooltip"
                x-text="collapsed ? 'Expand' : 'Collapse'"
            ></span>
        </button>
    </div>
</aside>
