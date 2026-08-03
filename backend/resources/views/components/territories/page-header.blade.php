@props([
    'title' => 'Territories',
    'description' => '',
    'viewMode' => 'table',
    'canCreate' => false,
    'csvUrl' => null,
    'excelUrl' => null,
    'pdfUrl' => null,
])

<section class="vestra-workspace__hero vestra-territories__hero">
    <div class="vestra-territories__hero-copy">
        <h1 class="vestra-workspace__title">{{ $title }}</h1>
        @if ($description)
            <p class="vestra-workspace__welcome">{{ $description }}</p>
        @endif
    </div>

    <div class="vestra-workspace__quick-actions vestra-territories__header-actions">
        <div class="vestra-territories__view-toggle" role="group" aria-label="Switch between table and map view">
            <button
                type="button"
                wire:click="setViewMode('table')"
                class="vestra-territories__view-toggle-btn @if ($viewMode === 'table') vestra-territories__view-toggle-btn--active @endif"
                aria-pressed="{{ $viewMode === 'table' ? 'true' : 'false' }}"
            >
                <x-filament::icon icon="heroicon-o-table-cells" class="h-4 w-4" />
                <span>Table</span>
            </button>
            <button
                type="button"
                wire:click="setViewMode('map')"
                class="vestra-territories__view-toggle-btn @if ($viewMode === 'map') vestra-territories__view-toggle-btn--active @endif"
                aria-pressed="{{ $viewMode === 'map' ? 'true' : 'false' }}"
            >
                <x-filament::icon icon="heroicon-o-map" class="h-4 w-4" />
                <span>Map</span>
            </button>
        </div>

        <div class="vestra-territories__export-dropdown" x-data="{ open: false }" @click.outside="open = false">
            <button
                type="button"
                @click="open = !open"
                class="vestra-button vestra-button--secondary"
                aria-haspopup="true"
                aria-label="Export territories"
            >
                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" />
                <span>Export</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>

            <div x-show="open" x-transition class="vestra-territories__export-menu" role="menu">
                <a href="{{ $csvUrl }}" class="vestra-territories__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4" />
                    <span>Export CSV</span>
                </a>
                <a href="{{ $excelUrl }}" class="vestra-territories__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-table-cells" class="h-4 w-4" />
                    <span>Export Excel</span>
                </a>
                <a href="{{ $pdfUrl }}" class="vestra-territories__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-document-arrow-down" class="h-4 w-4" />
                    <span>Export PDF</span>
                </a>
            </div>
        </div>

        @if ($canCreate)
            <a href="{{ \App\Filament\Resources\DistributorBranchResource::getUrl('create') }}" class="vestra-button vestra-button--primary" aria-label="Add branch">
                <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
                <span>Add Branch</span>
            </a>
        @endif
    </div>
</section>
