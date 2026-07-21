@props([
    'heading' => null,
    'description' => null,
    'icon' => null,
])

<div {{
    $attributes->class([
        'fi-vestra-form-container rounded-xl border border-neutral-200 bg-white shadow-sm',
    ])
}}>
    @if ($heading || $description || $icon)
        <div class="border-b border-neutral-100 px-6 py-4">
            <div class="flex items-center gap-x-3">
                @if ($icon)
                    <x-filament::icon :icon="$icon" class="h-5 w-5 text-neutral-400" />
                @endif
                <div>
                    @if ($heading)
                        <h3 class="text-base font-semibold text-neutral-800">{{ $heading }}</h3>
                    @endif
                    @if ($description)
                        <p class="text-sm text-neutral-500">{{ $description }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>
</div>
