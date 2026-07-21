<x-filament-widgets::widget class="fi-wi-alerts">
    <x-filament::section heading="Action Items" icon="heroicon-o-bell-alert">
        <div class="space-y-3">
            @forelse ($this->getAlerts() as $alert)
                <a
                    href="{{ $alert['url'] }}"
                    class="group flex items-start gap-3 rounded-lg border border-neutral-200 bg-white p-3 shadow-sm transition-all hover:border-{{ $alert['type'] }}-300 hover:bg-{{ $alert['type'] }}-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-{{ $alert['type'] }}-400"
                >
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-{{ $alert['type'] }}-100 text-{{ $alert['type'] }}-500">
                        <x-filament::icon
                            :icon="$alert['icon']"
                            class="h-4 w-4"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-neutral-800 group-hover:text-{{ $alert['type'] }}-700">
                            {{ $alert['message'] }}
                        </p>
                        <p class="text-xs text-neutral-400">Click to view</p>
                    </div>
                </a>
            @empty
                <div class="flex flex-col items-center justify-center py-6 text-center">
                    <x-filament::icon
                        icon="heroicon-o-check-circle"
                        class="h-10 w-10 text-success-500"
                    />
                    <p class="mt-2 text-sm font-medium text-neutral-700">All caught up</p>
                    <p class="text-xs text-neutral-400">No urgent items require attention.</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
