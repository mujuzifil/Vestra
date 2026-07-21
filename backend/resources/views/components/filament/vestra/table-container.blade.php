@props([
    'heading' => null,
    'description' => null,
])

<div {{
    $attributes->class([
        'fi-vestra-table-container overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm',
    ])
}}>
    @if ($heading || $description)
        <div class="border-b border-neutral-100 px-6 py-4">
            @if ($heading)
                <h3 class="text-base font-semibold text-neutral-800">{{ $heading }}</h3>
            @endif
            @if ($description)
                <p class="text-sm text-neutral-500">{{ $description }}</p>
            @endif
        </div>
    @endif

    <div>
        {{ $slot }}
    </div>
</div>
