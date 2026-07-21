@props(['events' => []])

@php
    $colorMap = [
        'primary' => ['bg' => 'var(--primary-100)', 'text' => 'var(--primary-600)'],
        'success' => ['bg' => 'var(--success-100)', 'text' => 'var(--success-600)'],
        'danger' => ['bg' => 'var(--danger-100)', 'text' => 'var(--danger-600)'],
        'warning' => ['bg' => 'var(--warning-100)', 'text' => 'var(--warning-600)'],
        'info' => ['bg' => 'var(--info-100)', 'text' => 'var(--info-600)'],
        'gray' => ['bg' => 'var(--neutral-100)', 'text' => 'var(--neutral-600)'],
    ];
@endphp

<div class="flow-root">
    <ul role="list" class="-mb-2">
        @forelse ($events as $event)
            @php
                $colorStyles = $colorMap[$event['color'] ?? 'gray'] ?? $colorMap['gray'];
            @endphp
            <li class="relative pb-6 pl-8 last:pb-0">
                @if (! $loop->last)
                    <span class="absolute left-3 top-8 h-full w-px bg-neutral-200" aria-hidden="true"></span>
                @endif

                <div class="absolute left-0 top-1 flex h-7 w-7 items-center justify-center rounded-full ring-4 ring-white" style="background-color: {{ $colorStyles['bg'] }}">
                    <x-filament::icon
                        :icon="$event['icon']"
                        class="h-4 w-4"
                        style="color: {{ $colorStyles['text'] }}"
                    />
                </div>

                <div class="flex flex-col gap-0.5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-neutral-800">
                            {{ $event['title'] }}
                        </p>
                        @if ($event['description'] ?? false)
                            <p class="text-sm text-neutral-600">{{ $event['description'] }}</p>
                        @endif
                        @if ($event['actor'] ?? false)
                            <p class="mt-0.5 text-xs text-neutral-500">
                                by {{ $event['actor'] }}
                            </p>
                        @endif
                    </div>
                    <span class="mt-1 text-xs text-neutral-400 sm:mt-0">
                        @if ($event['time'] instanceof \Carbon\Carbon)
                            {{ $event['time']->diffForHumans() }}
                        @else
                            {{ $event['time'] }}
                        @endif
                    </span>
                </div>
            </li>
        @empty
            <li class="py-6 text-center">
                <x-filament::icon
                    icon="heroicon-o-inbox"
                    class="mx-auto h-10 w-10 text-neutral-300"
                />
                <p class="mt-2 text-sm text-neutral-500">No timeline events available</p>
            </li>
        @endforelse
    </ul>
</div>
