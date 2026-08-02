@props([
    'title' => 'Tasks',
    'description' => '',
    'tasks' => null,
])

<section class="vestra-workspace__hero">
    <div>
        <h1 class="vestra-workspace__title">{{ $title }}</h1>
        @if ($description)
            <p class="vestra-workspace__welcome">{{ $description }}</p>
        @endif
    </div>

    <div class="vestra-workspace__quick-actions vestra-tasks__header-actions">
        <button
            type="button"
            wire:click="$dispatch('task-import-opened')"
            class="vestra-button vestra-button--secondary"
            aria-label="Import tasks"
        >
            <x-filament::icon icon="heroicon-o-arrow-up-on-square" class="h-4 w-4" />
            <span>Import</span>
        </button>

        <button
            type="button"
            wire:click="$dispatch('task-export-requested')"
            class="vestra-button vestra-button--secondary"
            aria-label="Export tasks"
        >
            <x-filament::icon icon="heroicon-o-arrow-down-on-square" class="h-4 w-4" />
            <span>Export</span>
        </button>

        <button
            type="button"
            wire:click="openCreateDrawer"
            class="vestra-button vestra-button--primary"
            aria-label="Create new task"
        >
            <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
            <span>New Task</span>
        </button>
    </div>
</section>
