@props([
    'title' => 'Media Library',
    'description' => '',
    'viewMode' => 'grid',
    'csvUrl' => null,
    'excelUrl' => null,
    'pdfUrl' => null,
    'canUpload' => false,
])

<section class="vestra-workspace__hero vestra-media__hero">
    <div class="vestra-media__hero-main">
        <div>
            <h1 class="vestra-workspace__title">{{ $title }}</h1>
            @if ($description)
                <p class="vestra-workspace__welcome">{{ $description }}</p>
            @endif
        </div>

        <div class="vestra-media__search">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="vestra-media__search-icon" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search filename, product, blog, uploader, tags…"
                class="vestra-media__search-input"
                aria-label="Search media"
            />
        </div>
    </div>

    <div class="vestra-workspace__quick-actions vestra-media__header-actions">
        <div class="vestra-media__view-toggle" role="group" aria-label="Switch between grid and list view">
            <button
                type="button"
                wire:click="setViewMode('grid')"
                class="vestra-media__view-toggle-btn @if ($viewMode === 'grid') vestra-media__view-toggle-btn--active @endif"
                aria-pressed="{{ $viewMode === 'grid' ? 'true' : 'false' }}"
            >
                <x-filament::icon icon="heroicon-o-squares-2x2" class="h-4 w-4" />
                <span>Grid</span>
            </button>
            <button
                type="button"
                wire:click="setViewMode('list')"
                class="vestra-media__view-toggle-btn @if ($viewMode === 'list') vestra-media__view-toggle-btn--active @endif"
                aria-pressed="{{ $viewMode === 'list' ? 'true' : 'false' }}"
            >
                <x-filament::icon icon="heroicon-o-list-bullet" class="h-4 w-4" />
                <span>List</span>
            </button>
        </div>

        <button type="button" wire:click="$refresh" class="vestra-button vestra-button--secondary" aria-label="Refresh media">
            <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
            <span>Refresh</span>
        </button>

        <div class="vestra-media__export-dropdown" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-button vestra-button--secondary" aria-haspopup="true" :aria-expanded="open">
                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" />
                <span>Export</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" />
            </button>
            <div x-show="open" x-transition class="vestra-media__export-menu" role="menu">
                <a href="{{ $csvUrl }}" class="vestra-media__export-option" role="menuitem">Export CSV</a>
                <a href="{{ $excelUrl }}" class="vestra-media__export-option" role="menuitem">Export Excel</a>
                <a href="{{ $pdfUrl }}" class="vestra-media__export-option" role="menuitem">Export PDF</a>
            </div>
        </div>

        @if ($canUpload)
            <button type="button" wire:click="openUploadModal" class="vestra-button vestra-button--primary">
                <x-filament::icon icon="heroicon-o-arrow-up-tray" class="h-4 w-4" />
                <span>Upload Asset</span>
            </button>
        @endif
    </div>
</section>
