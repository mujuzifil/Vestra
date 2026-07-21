<div
    x-data="{ open: @entangle('isOpen') }"
    x-on:open-command-palette.window="open = true"
    x-on:keydown.escape.window="open = false"
>
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-neutral-900/50 backdrop-blur-sm"
        x-on:click="open = false"
        aria-hidden="true"
    ></div>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-start justify-center px-4 pt-[15vh]"
        role="dialog"
        aria-modal="true"
        aria-labelledby="command-palette-title"
    >
        <div
            class="w-full max-w-2xl overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-neutral-200"
            x-on:click.stop
        >
            <div class="relative border-b border-neutral-200">
                <x-filament::icon
                    icon="heroicon-o-magnifying-glass"
                    class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-neutral-400"
                />

                <input
                    type="text"
                    wire:model.live.debounce.200ms="query"
                    class="w-full border-0 bg-transparent py-4 pl-12 pr-24 text-neutral-800 placeholder-neutral-400 focus:ring-0"
                    placeholder="{{ __('Search orders, products, customers...') }}"
                    aria-label="{{ __('Global search') }}"
                    x-on:keydown.down.prevent="document.querySelector('.fi-command-result')?.focus()"
                />

                <div class="absolute right-4 top-1/2 hidden -translate-y-1/2 items-center gap-2 sm:flex">
                    <kbd class="rounded border border-neutral-200 bg-neutral-50 px-1.5 py-0.5 text-xs font-medium text-neutral-500">Esc</kbd>
                    <span class="text-xs text-neutral-400">to close</span>
                </div>
            </div>

            <div class="max-h-[60vh] overflow-y-auto">
                @if ($isLoading)
                    <div class="px-4 py-8 text-center">
                        <div class="mx-auto h-8 w-8 animate-spin rounded-full border-2 border-neutral-200 border-t-primary-500"></div>
                        <p class="mt-3 text-sm text-neutral-500">
                            {{ __('Searching...') }}
                        </p>
                    </div>
                @elseif (empty($results))
                    <div class="px-4 py-10 text-center">
                        <x-filament::icon
                            icon="heroicon-o-magnifying-glass"
                            class="mx-auto h-10 w-10 text-neutral-300"
                        />
                        <p class="mt-2 text-sm text-neutral-500">
                            {{ blank($query) ? __('Start typing to search...') : __('No results found for "') . $query . '"' }}
                        </p>
                    </div>
                @else
                    @foreach ($results as $group => $items)
                        <div class="border-b border-neutral-100 last:border-b-0">
                            <div class="bg-neutral-50 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                {{ $group }}
                            </div>

                            <ul role="listbox">
                                @foreach ($items as $index => $item)
                                    <li>
                                        <a
                                            href="{{ $item['url'] }}"
                                            class="fi-command-result group flex items-start gap-3 px-4 py-3 outline-none transition-colors hover:bg-neutral-50 focus:bg-neutral-50 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-400"
                                            tabindex="0"
                                            x-on:keydown.enter.prevent.stop="window.location.href = $el.href"
                                        >
                                            <x-filament::icon
                                                :icon="$item['icon']"
                                                class="mt-0.5 h-5 w-5 shrink-0 text-neutral-400 group-hover:text-neutral-600"
                                            />

                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-neutral-800">
                                                    {{ $item['title'] }}
                                                </p>
                                                <p class="text-xs text-neutral-500">
                                                    {{ $item['subtitle'] }}
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="border-t border-neutral-200 bg-neutral-50 px-4 py-2">
                <p class="text-xs text-neutral-400">
                    {{ __('Global search foundation — backend integration pending') }}
                </p>
            </div>
        </div>
    </div>
</div>
