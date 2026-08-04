@props([
    'title' => 'Roles',
    'description' => '',
    'csvUrl' => null,
    'excelUrl' => null,
    'pdfUrl' => null,
    'canCreate' => false,
    'createUrl' => null,
])

<section class="vestra-workspace__hero vestra-roles__hero">
    <div class="vestra-roles__hero-main">
        <div>
            <h1 class="vestra-workspace__title">{{ $title }}</h1>
            @if ($description)
                <p class="vestra-workspace__welcome">{{ $description }}</p>
            @endif
        </div>

        <div class="vestra-roles__search">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="vestra-roles__search-icon" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search roles, slugs..."
                class="vestra-roles__search-input"
                aria-label="Search Roles posts"
            />
        </div>
    </div>

    <div class="vestra-workspace__quick-actions vestra-roles__header-actions">
        <button
            type="button"
            wire:click="$refresh"
            class="vestra-button vestra-button--secondary"
            aria-label="Refresh roles"
        >
            <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
            <span>Refresh</span>
        </button>

        <div class="vestra-roles__export-dropdown" x-data="{ open: false }" @click.outside="open = false">
            <button
                type="button"
                @click="open = !open"
                class="vestra-button vestra-button--secondary"
                aria-haspopup="true"
                :aria-expanded="open"
                aria-label="Export roles"
            >
                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" />
                <span>Export</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>

            <div
                x-show="open"
                x-transition
                class="vestra-roles__export-menu"
                role="menu"
            >
                <a href="{{ $csvUrl }}" class="vestra-roles__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4" />
                    <span>Export CSV</span>
                </a>
                <a href="{{ $excelUrl }}" class="vestra-roles__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-table-cells" class="h-4 w-4" />
                    <span>Export Excel</span>
                </a>
                <a href="{{ $pdfUrl }}" class="vestra-roles__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-document-arrow-down" class="h-4 w-4" />
                    <span>Export PDF</span>
                </a>
            </div>
        </div>

        @if ($canCreate && $createUrl)
            <a href="{{ $createUrl }}" class="vestra-button vestra-button--primary" aria-label="Add role">
                <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
                <span>New role</span>
            </a>
        @endif
    </div>
</section>
