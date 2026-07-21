@props([
    'heading' => null,
    'description' => null,
])

<div {{
    $attributes->class([
        'fi-vestra-widget-container rounded-xl border border-neutral-200 bg-white p-6 shadow-sm',
    ])
}}>
    @if ($heading || $description)
        <div class="mb-4">
            @if ($heading)
                <h3 class="text-base font-semibold text-neutral-800">{{ $heading }}</h3>
            @endif
            @if ($description)
                <p class="text-sm text-neutral-500">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
