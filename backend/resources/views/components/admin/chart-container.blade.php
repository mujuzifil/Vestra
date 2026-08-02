@props([
    'id' => 'dashboard-chart',
    'title' => '',
    'labels' => [],
    'values' => [],
    'empty' => false,
])

<div class="vestra-card vestra-chart-card">
    <div class="vestra-card-header">
        <h3 class="vestra-card-title">{{ $title }}</h3>
    </div>

    @if ($empty)
        <x-admin.empty-state
            icon="heroicon-o-chart-bar"
            title="No data available yet"
            description="Sales data will appear here once quote activity is recorded."
        />
    @else
        <div class="vestra-chart-card__canvas-wrap" wire:ignore>
            <canvas id="{{ $id }}" height="320"></canvas>
        </div>
    @endif

    <script>
        window.__dashboardChartData = window.__dashboardChartData || {};
        window.__dashboardChartData['{{ $id }}'] = @json([
            'labels' => $labels,
            'values' => $values,
        ]);
    </script>
</div>
