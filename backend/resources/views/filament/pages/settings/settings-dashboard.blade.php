<x-filament-panels::page class="vestra-settings-page">
    <div class="settings-dashboard space-y-8">
        {{-- Header --}}
        <div>
            <p class="text-sm text-neutral-600">
                Manage business behaviour, branding, localization, notifications, and system settings from a single location.
            </p>
        </div>

        {{-- Navigation cards --}}
        <section aria-labelledby="settings-categories-heading">
            <h2 id="settings-categories-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">
                Configuration Groups
            </h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->getSettingGroups() as $group)
                    <a
                        href="{{ $this->getGroupRoute($group) }}"
                        class="group block rounded-xl border border-neutral-200 bg-white p-5 shadow-sm transition-all hover:border-primary-300 hover:shadow-md"
                    >
                        <div class="flex items-start gap-4">
                            <div class="rounded-lg bg-{{ $this->getGroupColor($group) }}-50 p-3">
                                <x-filament::icon :icon="$group->icon()" class="h-6 w-6 text-{{ $this->getGroupColor($group) }}-600" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-neutral-900 group-hover:text-primary-600">{{ $group->label() }}</h3>
                                <p class="mt-1 text-sm text-neutral-600">{{ $this->getGroupDescription($group) }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</x-filament-panels::page>
