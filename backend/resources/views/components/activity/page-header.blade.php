@props([
    'title' => 'Activity',
    'description' => '',
])

<section class="vestra-workspace__hero">
    <div>
        <h1 class="vestra-workspace__title">{{ $title }}</h1>
        @if ($description)
            <p class="vestra-workspace__welcome">{{ $description }}</p>
        @endif
    </div>

    <div class="vestra-workspace__quick-actions vestra-activity__header-actions">
        <button
            type="button"
            wire:click="$refresh"
            class="vestra-button vestra-button--secondary"
            aria-label="Refresh activity feed"
        >
            <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
            <span>Refresh</span>
        </button>

        <div class="vestra-activity__export-dropdown" x-data="{ open: false }" @click.outside="open = false">
            <button
                type="button"
                @click="open = !open"
                class="vestra-button vestra-button--secondary"
                aria-haspopup="true"
                aria-label="Export activity"
            >
                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" />
                <span>Export</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>

            <div
                x-show="open"
                x-transition
                class="vestra-activity__export-menu"
                role="menu"
            >
                <button type="button" wire:click="export('csv')" class="vestra-activity__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4" />
                    <span>Export CSV</span>
                </button>
                <button type="button" wire:click="export('excel')" class="vestra-activity__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-table-cells" class="h-4 w-4" />
                    <span>Export Excel</span>
                </button>
                <button type="button" wire:click="export('pdf')" class="vestra-activity__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-document-arrow-down" class="h-4 w-4" />
                    <span>Export PDF</span>
                </button>
            </div>
        </div>
    </div>
</section>
