<x-filament-widgets::widget class="fi-wi-quick-actions">
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ($this->getActions() as $action)
            @if ($action['disabled'] ?? false)
                <button
                    type="button"
                    disabled
                    class="flex flex-col items-center justify-center gap-2 rounded-xl border border-neutral-200 bg-white px-4 py-5 text-center opacity-60 shadow-sm transition-colors"
                >
                    <x-filament::icon
                        :icon="$action['icon']"
                        class="h-6 w-6 text-neutral-400"
                    />
                    <span class="text-sm font-medium text-neutral-500">{{ $action['label'] }}</span>
                </button>
            @else
                <a
                    href="{{ $action['url'] }}"
                    class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-neutral-200 bg-white px-4 py-5 text-center shadow-sm transition-all hover:border-primary-300 hover:bg-primary-50 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-400"
                >
                    <x-filament::icon
                        :icon="$action['icon']"
                        class="h-6 w-6 text-primary-500 transition-colors group-hover:text-primary-600"
                    />
                    <span class="text-sm font-semibold text-neutral-700 transition-colors group-hover:text-primary-700">{{ $action['label'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</x-filament-widgets::widget>
