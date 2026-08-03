@props([
    'title' => 'Companies',
    'description' => '',
    'csvUrl' => null,
    'excelUrl' => null,
    'pdfUrl' => null,
])

<section class="vestra-workspace__hero vestra-companies__hero">
    <div class="vestra-companies__hero-main">
        <div>
            <h1 class="vestra-workspace__title">{{ $title }}</h1>
            @if ($description)
                <p class="vestra-workspace__welcome">{{ $description }}</p>
            @endif
        </div>

        <div class="vestra-companies__search">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="vestra-companies__search-icon" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search companies, contacts, industry, email..."
                class="vestra-companies__search-input"
                aria-label="Search companies"
            />
        </div>
    </div>

    <div class="vestra-workspace__quick-actions vestra-companies__header-actions">
        <button
            type="button"
            wire:click="$refresh"
            class="vestra-button vestra-button--secondary"
            aria-label="Refresh companies"
        >
            <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
            <span>Refresh</span>
        </button>

        <button
            type="button"
            wire:click="$set('showImportDrawer', true)"
            class="vestra-button vestra-button--secondary"
            aria-label="Import companies"
        >
            <x-filament::icon icon="heroicon-o-arrow-up-on-square" class="h-4 w-4" />
            <span>Import</span>
        </button>

        <div class="vestra-companies__export-dropdown" x-data="{ open: false }" @click.outside="open = false">
            <button
                type="button"
                @click="open = !open"
                class="vestra-button vestra-button--secondary"
                aria-haspopup="true"
                aria-label="Export companies"
            >
                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" />
                <span>Export</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>

            <div
                x-show="open"
                x-transition
                class="vestra-companies__export-menu"
                role="menu"
            >
                <a href="{{ $csvUrl }}" class="vestra-companies__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4" />
                    <span>Export CSV</span>
                </a>
                <a href="{{ $excelUrl }}" class="vestra-companies__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-table-cells" class="h-4 w-4" />
                    <span>Export Excel</span>
                </a>
                <a href="{{ $pdfUrl }}" class="vestra-companies__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-document-arrow-down" class="h-4 w-4" />
                    <span>Export PDF</span>
                </a>
            </div>
        </div>

        <button
            type="button"
            wire:click="openCreateDrawer"
            class="vestra-button vestra-button--primary"
            aria-label="Create new company"
        >
            <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
            <span>New Company</span>
        </button>
    </div>
</section>
