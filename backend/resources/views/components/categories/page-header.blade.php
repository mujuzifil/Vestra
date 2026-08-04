@props([
    'title' => 'Categories',
    'description' => '',
    'canCreate' => false,
    'createUrl' => null,
    'csvUrl' => null,
    'excelUrl' => null,
    'pdfUrl' => null,
])

<section class="vestra-workspace__hero vestra-categories__hero">
    <div class="vestra-categories__hero-main">
        <div>
            <h1 class="vestra-workspace__title">{{ $title }}</h1>
            @if ($description)
                <p class="vestra-workspace__welcome">{{ $description }}</p>
            @endif
        </div>

        <div class="vestra-categories__search">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="vestra-categories__search-icon" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search categories by name, slug..."
                class="vestra-categories__search-input"
                aria-label="Search categories"
            />
        </div>
    </div>

    <div class="vestra-workspace__quick-actions vestra-categories__header-actions">
        <button
            type="button"
            wire:click="$refresh"
            class="vestra-button vestra-button--secondary"
            aria-label="Refresh categories"
        >
            <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
            <span>Refresh</span>
        </button>

        <div class="vestra-categories__export-dropdown" x-data="{ open: false }" @click.outside="open = false">
            <button
                type="button"
                @click="open = !open"
                class="vestra-button vestra-button--secondary"
                aria-haspopup="true"
                :aria-expanded="open.toString()"
                aria-label="Export categories"
            >
                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" />
                <span>Export</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" x-bind:class="{ 'rotate-180': open }" />
            </button>

            <div
                x-show="open"
                x-transition
                class="vestra-categories__export-menu"
                role="menu"
            >
                <a href="{{ $csvUrl }}" class="vestra-categories__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4" />
                    <span>Export CSV</span>
                </a>
                <a href="{{ $excelUrl }}" class="vestra-categories__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-table-cells" class="h-4 w-4" />
                    <span>Export Excel</span>
                </a>
                <a href="{{ $pdfUrl }}" class="vestra-categories__export-option" role="menuitem">
                    <x-filament::icon icon="heroicon-o-document-arrow-down" class="h-4 w-4" />
                    <span>Export PDF</span>
                </a>
            </div>
        </div>

        @if ($canCreate && $createUrl)
            <a href="{{ $createUrl }}" class="vestra-button vestra-button--primary" aria-label="Add category">
                <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
                <span>Add Category</span>
            </a>
        @endif
    </div>
</section>
