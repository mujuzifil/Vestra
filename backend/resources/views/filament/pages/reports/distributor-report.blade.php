<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Monitor distributor applications, approval rates, and geographic distribution.
            </p>
        </div>

        <x-filament::section icon="heroicon-o-funnel" heading="Filters">
            {{ $this->form }}
        </x-filament::section>

        @php
            $summary = $this->getDistributorSummary();
            $byStatus = $this->getApplicationsByStatus();
            $byCountry = $this->getApplicationsByCountry();
            $byRegion = $this->getApplicationsByRegion();
            $trend = $this->getDistributorTrend();
        @endphp

        <section aria-labelledby="distributor-kpis-heading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <h2 id="distributor-kpis-heading" class="sr-only">Distributor KPIs</h2>
            <x-reports.report-kpi-card label="Total Applications" :value="number_format($summary['total_applications'])" icon="heroicon-o-truck" color="primary" />
            <x-reports.report-kpi-card label="Pending Review" :value="number_format($summary['pending_review'])" icon="heroicon-o-clock" color="warning" />
            <x-reports.report-kpi-card label="Approved" :value="number_format($summary['approved'])" icon="heroicon-o-check-circle" color="success" />
            <x-reports.report-kpi-card label="Rejected" :value="number_format($summary['rejected'])" icon="heroicon-o-x-circle" color="danger" />
            <x-reports.report-kpi-card label="Approval Rate" :value="$summary['approval_rate'] . '%'" icon="heroicon-o-chart-pie" color="info" />
        </section>

        <section aria-labelledby="distributor-trend-heading">
            <h2 id="distributor-trend-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Application Trend</h2>
            @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                'chartType' => 'line',
                'chartHeading' => 'Applications Over Time',
                'chartLabels' => $trend['labels'],
                'chartDatasets' => [[
                    'label' => 'Applications',
                    'data' => $trend['applications'],
                    'fill' => true,
                    'tension' => 0.4,
                    'borderColor' => '#0d3b66',
                    'backgroundColor' => 'rgba(13, 59, 102, 0.1)',
                    'pointBackgroundColor' => '#0d3b66',
                ]],
            ])
        </section>

        <section aria-labelledby="distributor-breakdown-heading">
            <h2 id="distributor-breakdown-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Application Breakdown</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                @php
                    $statusLabels = array_column($byStatus, 'status');
                    $statusValues = array_column($byStatus, 'count');
                    $statusColors = ['#d4af37', '#4a90d9', '#60a5fa', '#70c050', '#dc2626'];
                @endphp
                @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                    'chartType' => 'doughnut',
                    'chartHeading' => 'By Status',
                    'chartLabels' => $statusLabels,
                    'chartDatasets' => [[
                        'label' => 'Applications',
                        'data' => $statusValues,
                        'backgroundColor' => array_slice($statusColors, 0, count($statusValues)),
                        'borderWidth' => 0,
                    ]],
                ])

                @php
                    $countryLabels = array_column($byCountry, 'country');
                    $countryValues = array_column($byCountry, 'count');
                @endphp
                @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                    'chartType' => 'bar',
                    'chartHeading' => 'By Country',
                    'chartLabels' => $countryLabels,
                    'chartDatasets' => [[
                        'label' => 'Applications',
                        'data' => $countryValues,
                        'backgroundColor' => '#0d3b66',
                        'borderRadius' => 4,
                    ]],
                ])

                @php
                    $regionLabels = array_column($byRegion, 'region');
                    $regionValues = array_column($byRegion, 'count');
                @endphp
                @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                    'chartType' => 'bar',
                    'chartHeading' => 'By Region',
                    'chartLabels' => $regionLabels,
                    'chartDatasets' => [[
                        'label' => 'Applications',
                        'data' => $regionValues,
                        'backgroundColor' => '#70c050',
                        'borderRadius' => 4,
                    ]],
                ])
            </div>
        </section>

        <section aria-labelledby="distributor-tables-heading">
            <h2 id="distributor-tables-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Detailed Data</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-reports.report-table
                    :columns="[['name' => 'country', 'label' => 'Country'], ['name' => 'count', 'label' => 'Applications']]"
                    :rows="$byCountry"
                    emptyHeading="No country data"
                />
                <x-reports.report-table
                    :columns="[['name' => 'region', 'label' => 'Region'], ['name' => 'count', 'label' => 'Applications']]"
                    :rows="$byRegion"
                    emptyHeading="No region data"
                />
            </div>
        </section>
    </div>
</x-filament-panels::page>
