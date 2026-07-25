<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Customer segmentation, lifetime value, retention, churn, and regional distribution.
            </p>
        </div>

        <x-filament::section icon="heroicon-o-funnel" heading="Filters">
            {{ $this->form }}
        </x-filament::section>

        @php
            $segments = $this->getCustomerSegments();
            $clv = $this->getCustomerLifetimeValue();
            $retention = $this->getRetentionRate();
            $churn = $this->getChurnRate();
            $regions = $this->getTopRegions();
        @endphp

        <section aria-labelledby="customer-intelligence-kpis-heading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <h2 id="customer-intelligence-kpis-heading" class="sr-only">Customer Intelligence KPIs</h2>
            <x-reports.report-kpi-card label="Retention Rate" :value="number_format($retention, 1) . '%'" icon="heroicon-o-arrow-uturn-left" color="success" />
            <x-reports.report-kpi-card label="Churn Rate (90d)" :value="number_format($churn, 1) . '%'" icon="heroicon-o-user-minus" color="danger" />
            <x-reports.report-kpi-card label="Top CLV" :value="'UGX ' . number_format($clv[0]['clv'] ?? 0)" icon="heroicon-o-trophy" color="primary" />
            <x-reports.report-kpi-card label="Top Region" :value="$regions[0]['country'] ?? '—'" icon="heroicon-o-globe-alt" color="info" />
        </section>

        <section aria-labelledby="customer-segments-heading">
            <h2 id="customer-segments-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Customer Segments</h2>
            @php
                $segmentLabels = array_column($segments, 'segment');
                $segmentValues = array_column($segments, 'count');
                $segmentColors = ['#0d3b66', '#70c050', '#d4af37', '#f59e0b', '#dc2626'];
            @endphp
            @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                'chartType' => 'doughnut',
                'chartHeading' => 'Segment Distribution',
                'chartLabels' => $segmentLabels,
                'chartDatasets' => [[
                    'label' => 'Customers',
                    'data' => $segmentValues,
                    'backgroundColor' => array_slice($segmentColors, 0, count($segmentValues)),
                    'borderWidth' => 0,
                ]],
            ])
        </section>

        <section aria-labelledby="customer-intelligence-tables-heading">
            <h2 id="customer-intelligence-tables-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Detailed Data</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-reports.report-table
                    :columns="[['name' => 'name', 'label' => 'Customer'], ['name' => 'email', 'label' => 'Email'], ['name' => 'orders', 'label' => 'Orders'], ['name' => 'clv', 'label' => 'Lifetime Value (UGX)']]"
                    :rows="$clv"
                    emptyHeading="No CLV data"
                />
                <x-reports.report-table
                    :columns="[['name' => 'country', 'label' => 'Country'], ['name' => 'orders', 'label' => 'Orders'], ['name' => 'revenue', 'label' => 'Revenue (UGX)']]"
                    :rows="$regions"
                    emptyHeading="No regional data"
                />
            </div>
        </section>
    </div>
</x-filament-panels::page>
