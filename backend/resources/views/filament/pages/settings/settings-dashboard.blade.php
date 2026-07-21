<x-filament-panels::page class="vestra-settings-page">
    <div class="settings-dashboard space-y-8">
        {{-- Header --}}
        <div>
            <p class="text-sm text-neutral-600">
                Manage business behaviour, branding, localization, notifications, and system settings from a single location.
            </p>
        </div>

        {{-- Search --}}
        <section aria-labelledby="settings-search-heading">
            <h2 id="settings-search-heading" class="sr-only">Search settings</h2>
            <form wire:submit="searchSettings" class="max-w-2xl">
                <label for="settings-search" class="sr-only">Search settings</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-5 w-5 text-neutral-400" />
                    </div>
                    <input
                        id="settings-search"
                        type="search"
                        wire:model.live.debounce.300ms="data.search"
                        placeholder="Search by keyword, label, or group..."
                        class="block w-full rounded-lg border border-neutral-300 bg-white py-3 pl-10 pr-4 text-sm text-neutral-900 placeholder-neutral-400 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                    >
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                        <button
                            type="submit"
                            class="rounded-md bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                        >
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </section>

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

        {{-- Legacy list link --}}
        <section aria-labelledby="settings-legacy-heading">
            <h2 id="settings-legacy-heading" class="sr-only">Legacy settings list</h2>
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="font-semibold text-neutral-900">All Settings</h3>
                        <p class="mt-1 text-sm text-neutral-600">
                            Browse every configuration value in a single searchable table.
                        </p>
                    </div>
                    <a
                        href="{{ \App\Filament\Resources\SettingResource::getUrl() }}"
                        class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm ring-1 ring-inset ring-neutral-300 hover:bg-neutral-50"
                    >
                        Open settings table
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
