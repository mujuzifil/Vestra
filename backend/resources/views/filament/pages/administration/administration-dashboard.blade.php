<x-filament-panels::page class="vestra-administration-page">
    <div class="administration-dashboard space-y-8">
        {{-- Header --}}
        <div>
            <p class="text-sm text-neutral-600">
                Govern users, roles, permissions, sessions, audit history, and platform security from a single location.
            </p>
        </div>

        {{-- Search --}}
        <section aria-labelledby="admin-search-heading">
            <h2 id="admin-search-heading" class="sr-only">Search administration</h2>
            <form wire:submit="searchAdministration" class="max-w-2xl">
                <label for="admin-search" class="sr-only">Search administration</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-5 w-5 text-neutral-400" />
                    </div>
                    <input
                        id="admin-search"
                        type="search"
                        wire:model.live.debounce.300ms="data.term"
                        placeholder="Search users, roles, permissions, audit logs..."
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

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="text-xs text-neutral-500">Search in:</span>
                @foreach ($this->getSearchTargets($this->data['term'] ?? '') as $target)
                    <a
                        href="{{ $target['route'] }}"
                        class="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-xs font-medium text-neutral-700 shadow-sm ring-1 ring-inset ring-neutral-300 hover:bg-neutral-50"
                    >
                        <x-filament::icon :icon="$target['icon']" class="h-3.5 w-3.5" />
                        {{ $target['label'] }}
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Navigation cards --}}
        <section aria-labelledby="admin-categories-heading">
            <h2 id="admin-categories-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">
                Administration Areas
            </h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->getAdminLinks() as $link)
                    <a
                        href="{{ $link['route'] }}"
                        @if ($link['disabled'] ?? false) aria-disabled="true" tabindex="-1" @endif
                        class="group block rounded-xl border border-neutral-200 bg-white p-5 shadow-sm transition-all hover:border-primary-300 hover:shadow-md @if ($link['disabled'] ?? false) pointer-events-none opacity-60 @endif"
                    >
                        <div class="flex items-start gap-4">
                            <div class="rounded-lg bg-{{ $link['color'] }}-50 p-3">
                                <x-filament::icon :icon="$link['icon']" class="h-6 w-6 text-{{ $link['color'] }}-600" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-neutral-900 group-hover:text-primary-600">{{ $link['label'] }}</h3>
                                <p class="mt-1 text-sm text-neutral-600">{{ $link['description'] }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</x-filament-panels::page>
