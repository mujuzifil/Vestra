@props([
    'hasFilters' => false,
    'canCreate' => false,
    'createUrl' => null,
])

<div class="vestra-categories__empty">
    <span class="vestra-categories__empty-icon">
        <x-filament::icon icon="heroicon-o-tag" class="h-8 w-8" />
    </span>

    <h4 class="vestra-categories__empty-title">
        @if ($hasFilters)
            No categories found
        @else
            No categories yet
        @endif
    </h4>

    <p class="vestra-categories__empty-description">
        @if ($hasFilters)
            Try adjusting your filters to find what you're looking for.
        @else
            Create your first product category to organize the catalog.
        @endif
    </p>

    @if (! $hasFilters && $canCreate && $createUrl)
        <a href="{{ $createUrl }}" class="vestra-button vestra-button--primary" aria-label="Add category">
            <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
            <span>Add Category</span>
        </a>
    @endif
</div>
