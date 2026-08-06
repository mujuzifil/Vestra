@props([
    'livewire' => null,
])

<x-filament-panels::layout.base :livewire="$livewire">
    <div
        x-data="{
            mobileSidebarOpen: false,
            sidebarCollapsed: JSON.parse(localStorage.getItem('vestra-sidebar-collapsed') || 'false'),
        }"
        @toggle-mobile-sidebar.window="mobileSidebarOpen = !mobileSidebarOpen"
        @toggle-sidebar-collapse.window="if (window.innerWidth >= 1024) { sidebarCollapsed = !sidebarCollapsed }"
        x-init="
            $watch('sidebarCollapsed', value => localStorage.setItem('vestra-sidebar-collapsed', JSON.stringify(value)));
            try {
                const savedScroll = sessionStorage.getItem('vestra-sidebar-scroll');
                if (savedScroll !== null) {
                    $nextTick(() => {
                        const nav = document.querySelector('.vestra-sidebar__nav');
                        if (nav) {
                            nav.scrollTop = parseInt(savedScroll, 10) || 0;
                        }
                    });
                }
            } catch (error) {
                // Ignore storage failures.
            }
        "
        class="vestra-crm"
        :class="{
            'vestra-crm--sidebar-open': mobileSidebarOpen,
            'vestra-crm--sidebar-collapsed': sidebarCollapsed
        }"
        x-cloak
    >
        {{-- Mobile overlay --}}
        <div
            x-show="mobileSidebarOpen"
            @click="mobileSidebarOpen = false"
            x-transition.opacity
            class="vestra-crm__overlay lg:hidden"
        ></div>

        <x-admin.sidebar />

        <div class="vestra-crm__main">
            <x-admin.header :date-range="$livewire?->dateRange ?? 'this-week'" />

            <x-admin.content-shell>
                {{ $slot }}
            </x-admin.content-shell>
        </div>

        @livewire(\App\Livewire\Admin\GlobalSearchCommandPalette::class)
    </div>
</x-filament-panels::layout.base>
