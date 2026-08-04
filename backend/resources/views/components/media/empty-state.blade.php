@props([
    'hasFilters' => false,
    'canUploadProduct' => false,
    'blogUploadUrl' => null,
    'productUploadUrl' => null,
])

<div class="vestra-media__empty">
    <x-filament::icon icon="heroicon-o-photo" class="vestra-media__empty-icon" />
    @if ($hasFilters)
        <h3 class="vestra-media__empty-title">No files match your filters</h3>
        <p class="vestra-media__empty-description">Try adjusting or resetting your filters to see results.</p>
        <button type="button" wire:click="resetFilters" class="vestra-button vestra-button--primary">
            Reset Filters
        </button>
    @else
        <h3 class="vestra-media__empty-title">No media files yet</h3>
        <p class="vestra-media__empty-description">Files uploaded to blog posts, products, or settings will appear here automatically.</p>
        <div class="vestra-media__empty-actions">
            <a href="{{ $blogUploadUrl }}" class="vestra-button vestra-button--primary">
                New Blog Post
            </a>
            @if ($canUploadProduct)
                <a href="{{ $productUploadUrl }}" class="vestra-button vestra-button--secondary">
                    New Product
                </a>
            @endif
        </div>
    @endif
</div>
