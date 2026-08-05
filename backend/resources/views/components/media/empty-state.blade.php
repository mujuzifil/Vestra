@props([
    'hasFilters' => false,
    'canUpload' => false,
])

<div class="vestra-media__empty">
    <x-filament::icon icon="heroicon-o-photo" class="vestra-media__empty-icon" />
    @if ($hasFilters)
        <h3 class="vestra-media__empty-title">No assets match your filters</h3>
        <p class="vestra-media__empty-description">Try adjusting or resetting your filters to see results.</p>
        <button type="button" wire:click="resetFilters" class="vestra-button vestra-button--primary">Reset Filters</button>
    @else
        <h3 class="vestra-media__empty-title">No media assets yet</h3>
        <p class="vestra-media__empty-description">Upload images and documents to the Media Library so Products, Blog, and other modules can reuse them.</p>
        @if ($canUpload)
            <button type="button" wire:click="openUploadModal" class="vestra-button vestra-button--primary">Upload Asset</button>
        @endif
    @endif
</div>
