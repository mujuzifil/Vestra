@props([
    'heading' => null,
    'subheading' => null,
    'badge' => null,
    'badgeColor' => 'gray',
])

<div {{
    $attributes->class([
        'fi-vestra-detail-container space-y-6',
    ])
}}>
    @if ($heading || $subheading)
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-x-3">
                    @if ($heading)
                        <h2 class="text-xl font-semibold text-neutral-800">{{ $heading }}</h2>
                    @endif
                    @if ($badge)
                        <x-filament::badge :color="$badgeColor">{{ $badge }}</x-filament::badge>
                    @endif
                </div>
                @if ($subheading)
                    <p class="mt-1 text-sm text-neutral-500">{{ $subheading }}</p>
                @endif
            </div>
            {{ $actions ?? '' }}
        </div>
    @endif

    {{ $slot }}
</div>
