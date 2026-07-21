<x-filament-widgets::widget class="fi-wi-recent-activity">
    <x-filament::section heading="Recent Activity" icon="heroicon-o-clock">
        <div class="flow-root">
            <ul role="list" class="-mb-2">
                @forelse ($this->getActivities() as $activity)
                    <li class="relative pb-6 pl-6 last:pb-0">
                        @if (! $loop->last)
                            <span class="absolute left-2 top-8 h-full w-px bg-neutral-200" aria-hidden="true"></span>
                        @endif

                        <div class="absolute left-0 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-{{ $activity['color'] }}-100 ring-4 ring-white">
                            <x-filament::icon
                                :icon="$activity['icon']"
                                class="h-3 w-3 text-{{ $activity['color'] }}-600"
                            />
                        </div>

                        <div class="flex flex-col gap-0.5 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm text-neutral-700">
                                <span class="font-semibold">{{ $activity['actor'] }}</span>
                                <span class="text-neutral-500">{{ $activity['action'] }}</span>
                                @if ($activity['subject'])
                                    <span class="font-medium text-neutral-800">{{ $activity['subject'] }}</span>
                                @endif
                            </p>
                            <span class="text-xs text-neutral-400">{{ $activity['time'] }}</span>
                        </div>
                    </li>
                @empty
                    <li class="py-6 text-center">
                        <x-filament::icon
                            icon="heroicon-o-inbox"
                            class="mx-auto h-10 w-10 text-neutral-300"
                        />
                        <p class="mt-2 text-sm text-neutral-500">No recent activity</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
