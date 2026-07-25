<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                API request volume, top endpoints, error rates, latency, and authentication failures.
            </p>
        </div>

        <x-filament::section icon="heroicon-o-funnel" heading="Filters">
            {{ $this->form }}
        </x-filament::section>

        @php
            $analytics = $this->getApiAnalytics();
            $volume = $analytics['volume'];
            $errorRate = $analytics['error_rate'];
            $latency = $analytics['average_latency'];
            $auth = $analytics['auth_failure_rate'];
        @endphp

        <section aria-labelledby="api-kpis-heading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <h2 id="api-kpis-heading" class="sr-only">API KPIs</h2>
            <x-reports.report-kpi-card label="Total Requests" :value="number_format(array_sum($volume['requests']))" icon="heroicon-o-signal" color="primary" />
            <x-reports.report-kpi-card label="Avg Latency" :value="number_format(array_sum($latency['avg_latency_ms']) / max(1, count($latency['avg_latency_ms'])), 0) . ' ms'" icon="heroicon-o-clock" color="info" />
            <x-reports.report-kpi-card label="Error Rate" :value="number_format(array_sum($errorRate['error_rate']) / max(1, count($errorRate['error_rate'])), 2) . '%'" icon="heroicon-o-exclamation-triangle" color="danger" />
            <x-reports.report-kpi-card label="Auth Failure Rate" :value="number_format($auth['failure_rate'], 2) . '%'" icon="heroicon-o-shield-exclamation" color="warning" />
        </section>

        <section aria-labelledby="api-volume-heading">
            <h2 id="api-volume-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Request Volume</h2>
            @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                'chartType' => 'bar',
                'chartHeading' => 'Requests Per Day',
                'chartLabels' => $volume['labels'],
                'chartDatasets' => [[
                    'label' => 'Requests',
                    'data' => $volume['requests'],
                    'backgroundColor' => '#0d3b66',
                    'borderRadius' => 4,
                ]],
            ])
        </section>

        <section aria-labelledby="api-top-endpoints-heading">
            <h2 id="api-top-endpoints-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Top Endpoints</h2>
            <x-reports.report-table
                :columns="[['name' => 'method', 'label' => 'Method'], ['name' => 'path', 'label' => 'Path'], ['name' => 'count', 'label' => 'Requests'], ['name' => 'avg_duration_ms', 'label' => 'Avg (ms)'], ['name' => 'errors', 'label' => 'Errors']]"
                :rows="$analytics['top_endpoints']"
                emptyHeading="No endpoint data"
            />
        </section>
    </div>
</x-filament-panels::page>
