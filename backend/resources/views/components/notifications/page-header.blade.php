@props([
    'title' => 'Notifications',
    'description' => '',
    'hasUnread' => false,
])

<section class="vestra-workspace__hero">
    <div>
        <h1 class="vestra-workspace__title">{{ $title }}</h1>
        @if ($description)
            <p class="vestra-workspace__welcome">{{ $description }}</p>
        @endif
    </div>

    <div class="vestra-workspace__quick-actions vestra-notifications__header-actions">
        <button
            type="button"
            wire:click="$refresh"
            class="vestra-button vestra-button--secondary"
            aria-label="Refresh notifications"
        >
            <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
            <span>Refresh</span>
        </button>

        @if ($hasUnread)
            <button
                type="button"
                wire:click="markAllRead"
                class="vestra-button vestra-button--secondary"
                aria-label="Mark all notifications as read"
            >
                <x-filament::icon icon="heroicon-o-check" class="h-4 w-4" />
                <span>Mark All Read</span>
            </button>
        @endif
    </div>
</section>
