@props([
    'unmappedCount' => 0,
])

<div class="vestra-territories__map-empty">
    <div class="vestra-territories__map-empty-canvas" aria-hidden="true">
        <div class="vestra-territories__map-grid"></div>
        <x-filament::icon icon="heroicon-o-map" class="vestra-territories__map-empty-icon" />
    </div>

    <h4 class="vestra-territories__empty-title">No geocoded branches yet</h4>

    <p class="vestra-territories__empty-description">
        @if ($unmappedCount > 0)
            {{ $unmappedCount }} {{ Str::plural('branch', $unmappedCount) }} matched your filters but {{ $unmappedCount === 1 ? 'has' : 'have' }} no latitude/longitude on file.
            Add coordinates on the branch record to plot it here — we never display estimated or placeholder pins.
        @else
            None of the branches matching your filters have latitude/longitude coordinates on file.
            Add coordinates to a branch record to plot it here — we never display estimated or placeholder pins.
        @endif
    </p>
</div>
