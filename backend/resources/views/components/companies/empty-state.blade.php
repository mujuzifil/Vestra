@props([
    'hasFilters' => false,
])

<div class="vestra-companies__empty">
    <span class="vestra-companies__empty-icon">
        <x-filament::icon icon="heroicon-o-building-office" class="h-8 w-8" />
    </span>

    <h4 class="vestra-companies__empty-title">
        @if ($hasFilters)
            No companies found
        @else
            No companies yet
        @endif
    </h4>

    <p class="vestra-companies__empty-description">
        @if ($hasFilters)
            Try adjusting your filters to find what you're looking for.
        @else
            Companies will appear here once they are added to the CRM. Create your first company to get started.
        @endif
    </p>

    @if (! $hasFilters)
        <button type="button" wire:click="openCreateDrawer" class="vestra-button vestra-button--primary vestra-companies__empty-action">
            <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
            <span>New Company</span>
        </button>
    @endif
</div>
