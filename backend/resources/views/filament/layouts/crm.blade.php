@props([
    'livewire' => null,
])

<x-filament-panels::layout.base :livewire="$livewire">
    <div
        x-data="{ mobileSidebarOpen: false }"
        @toggle-mobile-sidebar.window="mobileSidebarOpen = !mobileSidebarOpen"
        class="vestra-crm"
        :class="{ 'vestra-crm--sidebar-open': mobileSidebarOpen }"
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
    </div>
</x-filament-panels::layout.base>
