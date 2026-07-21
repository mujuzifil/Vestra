@props([
    'label',
    'value',
    'description' => null,
    'icon' => null,
    'color' => 'primary',
])

@php
    $colorClasses = match ($color) {
        'success' => 'bg-success-50 border-success-200 text-success-900',
        'warning' => 'bg-warning-50 border-warning-200 text-warning-900',
        'danger' => 'bg-danger-50 border-danger-200 text-danger-900',
        'info' => 'bg-info-50 border-info-200 text-info-900',
        default => 'bg-white border-neutral-200 text-neutral-900',
    };

    $iconColor = match ($color) {
        'success' => 'text-success-600',
        'warning' => 'text-warning-600',
        'danger' => 'text-danger-600',
        'info' => 'text-info-600',
        default => 'text-primary-600',
    };
@endphp

<div class="rounded-xl border p-5 shadow-sm transition-shadow hover:shadow-md {{ $colorClasses }}">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500">{{ $label }}</p>
            <p class="mt-1 text-2xl font-bold">{{ $value }}</p>
            @if ($description)
                <p class="mt-1 text-xs text-neutral-600">{{ $description }}</p>
            @endif
        </div>
        @if ($icon)
            <x-filament::icon :icon="$icon" class="h-6 w-6 {{ $iconColor }}" />
        @endif
    </div>
</div>
