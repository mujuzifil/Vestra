@props([
    'hasFilters' => false,
    'canCreate' => false,
])

<div class="vestra-territories__empty">
    <span class="vestra-territories__empty-icon">
        <x-filament::icon icon="heroicon-o-map" class="h-8 w-8" />
    </span>

    <h4 class="vestra-territories__empty-title">
        @if ($hasFilters)
            No branches found
        @else
            No branches yet
        @endif
    </h4>

    <p class="vestra-territories__empty-description">
        @if ($hasFilters)
            Try adjusting your filters to find what you're looking for.
        @else
            Distributor branches will appear here once they are registered.
        @endif
    </p>

    @if (! $hasFilters && $canCreate)
        <a href="{{ \App\Filament\Resources\DistributorBranchResource::getUrl('create') }}" class="vestra-button vestra-button--primary vestra-territories__empty-action">
            <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
            <span>Add Branch</span>
        </a>
    @endif
</div>
