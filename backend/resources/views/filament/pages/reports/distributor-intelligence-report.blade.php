<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Distributor revenue, credit utilization, outstanding balances, and performance trends.
            </p>
        </div>

        <x-filament::section icon="heroicon-o-funnel" heading="Filters">
            {{ $this->form }}
        </x-filament::section>

        @php
            $revenue = $this->getDistributorRevenue();
            $credit = $this->getCreditUtilization();
            $outstanding = $this->getOutstandingBalances();
            $trend = $this->getPerformanceTrend();
        @endphp

        <section aria-labelledby="distributor-finance-kpis-heading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <h2 id="distributor-finance-kpis-heading" class="sr-only">Distributor Finance KPIs</h2>
            <x-reports.report-kpi-card label="Total Outstanding" :value="'UGX ' . number_format($outstanding['total_outstanding'] ?? 0)" icon="heroicon-o-banknotes" color="danger" />
            <x-reports.report-kpi-card label="Top Distributor Revenue" :value="'UGX ' . number_format($revenue[0]['revenue'] ?? 0)" icon="heroicon-o-building-office" color="primary" />
            <x-reports.report-kpi-card label="Credit Accounts" :value="number_format(count($credit))" icon="heroicon-o-credit-card" color="info" />
            <x-reports.report-kpi-card label="Highest Utilization" :value="number_format($credit[0]['utilization_percentage'] ?? 0, 1) . '%'" icon="heroicon-o-chart-pie" color="warning" />
        </section>

        <section aria-labelledby="distributor-trend-heading">
            <h2 id="distributor-trend-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Distributor Performance Trend</h2>
            @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                'chartType' => 'line',
                'chartHeading' => 'Orders & Applications Over Time',
                'chartLabels' => $trend['labels'],
                'chartDatasets' => [
                    [
                        'label' => 'Orders',
                        'data' => $trend['orders'],
                        'borderColor' => '#0d3b66',
                        'backgroundColor' => 'rgba(13, 59, 102, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Applications',
                        'data' => $trend['applications'],
                        'borderColor' => '#70c050',
                        'backgroundColor' => 'rgba(112, 192, 80, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                ],
            ])
        </section>

        <section aria-labelledby="distributor-tables-heading">
            <h2 id="distributor-tables-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Detailed Data</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-reports.report-table
                    :columns="[['name' => 'company', 'label' => 'Distributor'], ['name' => 'orders', 'label' => 'Orders'], ['name' => 'revenue', 'label' => 'Revenue (UGX)']]"
                    :rows="$revenue"
                    emptyHeading="No distributor revenue data"
                />
                <x-reports.report-table
                    :columns="[['name' => 'distributor', 'label' => 'Distributor'], ['name' => 'limit', 'label' => 'Limit (UGX)'], ['name' => 'balance', 'label' => 'Balance (UGX)'], ['name' => 'utilization_percentage', 'label' => 'Utilization %']]"
                    :rows="$credit"
                    emptyHeading="No credit utilization data"
                />
            </div>
        </section>
    </div>
</x-filament-panels::page>
