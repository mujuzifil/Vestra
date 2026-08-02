@props([
    'icon' => 'heroicon-o-inbox',
    'title' => 'No data',
    'description' => null,
    'actionText' => null,
    'actionHref' => null,
])

<div class="flex flex-col items-center justify-center py-10 text-center">
    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--neutral-100)] text-[var(--neutral-400)]">
        <x-filament::icon :icon="$icon" class="h-7 w-7" />
    </span>

    <h4 class="mt-4 text-sm font-semibold text-[var(--text-heading)]">
        {{ $title }}
    </h4>

    @if ($description)
        <p class="mt-1 max-w-[18rem] text-xs text-[var(--text-muted)] leading-relaxed">
            {{ $description }}
        </p>
    @endif

    @if ($actionText && $actionHref)
        <a href="{{ $actionHref }}" class="mt-4 text-xs font-medium text-[var(--primary-500)] hover:text-[var(--primary-600)]">
            {{ $actionText }}
        </a>
    @endif
</div>
