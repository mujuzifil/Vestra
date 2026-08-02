@props([
    'icon' => 'heroicon-o-inbox',
    'title' => 'No data',
    'description' => null,
    'actionText' => null,
    'actionClick' => null,
    'showAction' => true,
])

<div class="vestra-tasks__empty">
    <span class="vestra-tasks__empty-icon">
        <x-filament::icon :icon="$icon" class="h-8 w-8" />
    </span>

    <h4 class="vestra-tasks__empty-title">{{ $title }}</h4>

    @if ($description)
        <p class="vestra-tasks__empty-description">{{ $description }}</p>
    @endif

    @if ($actionText && $actionClick && $showAction)
        <button type="button" wire:click="{{ $actionClick }}" class="vestra-button vestra-button--primary vestra-tasks__empty-action">
            <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
            <span>{{ $actionText }}</span>
        </button>
    @endif
</div>
