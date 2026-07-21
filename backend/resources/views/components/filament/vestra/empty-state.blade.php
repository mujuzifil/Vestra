@props([
    'icon' => 'heroicon-o-inbox',
    'heading' => null,
    'description' => null,
    'action' => null,
])

<div class="fi-vestra-empty-state flex flex-col items-center justify-center py-12 text-center">
    <div class="rounded-full bg-neutral-100 p-4">
        <x-filament::icon :icon="$icon" class="h-8 w-8 text-neutral-400" />
    </div>

    @if ($heading)
        <h3 class="mt-4 text-base font-semibold text-neutral-800">{{ $heading }}</h3>
    @endif

    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-neutral-500">{{ $description }}</p>
    @endif

    @if ($action)
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>
