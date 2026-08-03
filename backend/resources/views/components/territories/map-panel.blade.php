@props([
    'branches' => null,
    'unmappedCount' => 0,
])

@php
$branches = collect($branches ?? []);
$hasPins = $branches->isNotEmpty();

$lats = $branches->pluck('latitude');
$lngs = $branches->pluck('longitude');

$minLat = $lats->min();
$maxLat = $lats->max();
$minLng = $lngs->min();
$maxLng = $lngs->max();

$latSpan = ($maxLat - $minLat) ?: 1;
$lngSpan = ($maxLng - $minLng) ?: 1;

$statusColors = [
    'active' => 'success',
    'inactive' => 'gray',
];
@endphp

<div class="vestra-territories__map-card">
    @if ($hasPins)
        <div class="vestra-territories__map-toolbar">
            <div>
                <h3 class="vestra-territories__map-title">Branch Location Map</h3>
                <p class="vestra-territories__map-subtitle">
                    {{ $branches->count() }} geocoded {{ Str::plural('branch', $branches->count()) }} plotted from real latitude/longitude data.
                </p>
            </div>
            @if ($unmappedCount > 0)
                <span class="vestra-territories__map-unmapped">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-4 w-4" />
                    {{ $unmappedCount }} {{ Str::plural('branch', $unmappedCount) }} without coordinates
                </span>
            @endif
        </div>

        <div class="vestra-territories__map-canvas" role="img" aria-label="Proportional plot of branch coordinates">
            <div class="vestra-territories__map-grid"></div>

            @foreach ($branches as $branch)
                @php
                    $left = $lngSpan > 0 ? (($branch['longitude'] - $minLng) / $lngSpan) * 100 : 50;
                    $top = $latSpan > 0 ? (1 - (($branch['latitude'] - $minLat) / $latSpan)) * 100 : 50;
                    $left = min(96, max(4, $left));
                    $top = min(92, max(8, $top));
                    $color = $statusColors[$branch['status']] ?? 'gray';
                @endphp
                <button
                    type="button"
                    wire:click="openDetailDrawer({{ $branch['id'] }})"
                    class="vestra-territories__map-pin vestra-territories__map-pin--{{ $color }}"
                    style="left: {{ $left }}%; top: {{ $top }}%;"
                    title="{{ $branch['name'] }} ({{ number_format($branch['latitude'], 4) }}, {{ number_format($branch['longitude'], 4) }})"
                    aria-label="View {{ $branch['name'] }}"
                >
                    <x-filament::icon icon="heroicon-s-map-pin" class="h-6 w-6" />
                    <span class="vestra-territories__map-pin-label">{{ $branch['name'] }}</span>
                </button>
            @endforeach
        </div>

        <div class="vestra-territories__map-legend">
            <span class="vestra-territories__map-legend-item">
                <span class="vestra-territories__map-legend-dot vestra-territories__map-legend-dot--success"></span>
                Active
            </span>
            <span class="vestra-territories__map-legend-item">
                <span class="vestra-territories__map-legend-dot vestra-territories__map-legend-dot--gray"></span>
                Inactive
            </span>
        </div>
    @else
        <x-territories.map-empty-state :unmapped-count="$unmappedCount" />
    @endif
</div>
