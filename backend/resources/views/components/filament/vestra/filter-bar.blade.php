@props([
    'filters' => null,
    'search' => null,
    'actions' => null,
])

<div {{
    $attributes->class([
        'fi-vestra-filter-bar mb-6 flex flex-col gap-4 rounded-xl border border-neutral-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between',
    ])
}}>
    <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
        @if ($search)
            <div class="w-full max-w-sm">
                {{ $search }}
            </div>
        @endif

        @if ($filters)
            <div class="flex flex-wrap items-center gap-2">
                {{ $filters }}
            </div>
        @endif
    </div>

    @if ($actions)
        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
            {{ $actions }}
        </div>
    @endif
</div>
