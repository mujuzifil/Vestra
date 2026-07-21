<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Analyse revenue performance, payment methods, and order status contributions.
            </p>
        </div>

        {{-- Filters --}}
        <x-filament::section icon="heroicon-o-funnel" heading="Filters">
            {{ $this->form }}
        </x-filament::section>

        @php
            $summary = $this->getRevenueSummary();
            $trend = $this->getRevenueTrend();
            $byPayment = $this->getRevenueByPaymentMethod();
            $byStatus = $this->getRevenueByOrderStatus();
        @endphp

        {{-- KPIs --}}
        <section aria-labelledby="revenue-kpis-heading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <h2 id="revenue-kpis-heading" class="sr-only">Revenue KPIs</h2>
            <x-reports.report-kpi-card
                label="Total Revenue"
                :value="'UGX ' . number_format($summary['total_revenue'])"
                :description="$summary['change_percentage'] >= 0 ? '+' . $summary['change_percentage'] . '% vs previous period' : $summary['change_percentage'] . '% vs previous period'"
                icon="heroicon-o-banknotes"
                :color="$summary['change_percentage'] >= 0 ? 'success' : 'danger'"
            />
            <x-reports.report-kpi-card
                label="Orders"
                :value="number_format($summary['order_count'])"
                description="Paid orders in period"
                icon="heroicon-o-shopping-bag"
                color="info"
            />
            <x-reports.report-kpi-card
                label="Average Order Value"
                :value="'UGX ' . number_format($summary['average_order_value'])"
                description="Per paid order"
                icon="heroicon-o-calculator"
                color="primary"
            />
            <x-reports.report-kpi-card
                label="Previous Period"
                :value="'UGX ' . number_format($summary['previous_period_revenue'])"
                description="For comparison"
                icon="heroicon-o-clock"
                color="gray"
            />
        </section>

        {{-- Revenue Trend Chart --}}
        <section aria-labelledby="revenue-trend-heading">
            <h2 id="revenue-trend-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Revenue Trend</h2>
            @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                'chartType' => 'line',
                'chartHeading' => 'Revenue Over Time',
                'chartLabels' => $trend['labels'],
                'chartDatasets' => $this->getChartDatasets(),
            ])
        </section>

        {{-- Breakdown Charts --}}
        <section aria-labelledby="revenue-breakdown-heading">
            <h2 id="revenue-breakdown-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Revenue Breakdown</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                @php
                    $paymentLabels = array_column($byPayment, 'method');
                    $paymentValues = array_column($byPayment, 'revenue');
                    $paymentColors = ['#0d3b66', '#70c050', '#d4af37', '#dc2626', '#4a90d9'];
                    $paymentDatasets = [[
                        'label' => 'Revenue by Payment Method',
                        'data' => $paymentValues,
                        'backgroundColor' => array_slice($paymentColors, 0, count($paymentValues)),
                        'borderWidth' => 0,
                    ]];

                    $statusLabels = array_column($byStatus, 'status');
                    $statusValues = array_column($byStatus, 'revenue');
                    $statusColors = ['#d4af37', '#4a90d9', '#70c050', '#0d3b66', '#dc2626', '#64748b'];
                    $statusDatasets = [[
                        'label' => 'Revenue by Order Status',
                        'data' => $statusValues,
                        'backgroundColor' => array_slice($statusColors, 0, count($statusValues)),
                        'borderWidth' => 0,
                    ]];
                @endphp

                @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                    'chartType' => 'doughnut',
                    'chartHeading' => 'By Payment Method',
                    'chartLabels' => $paymentLabels,
                    'chartDatasets' => $paymentDatasets,
                ])

                @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                    'chartType' => 'bar',
                    'chartHeading' => 'By Order Status',
                    'chartLabels' => $statusLabels,
                    'chartDatasets' => $statusDatasets,
                ])
            </div>
        </section>

        {{-- Data Tables --}}
        <section aria-labelledby="revenue-tables-heading">
            <h2 id="revenue-tables-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Detailed Data</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-reports.report-table
                    :columns="[['name' => 'method', 'label' => 'Payment Method'], ['name' => 'revenue', 'label' => 'Revenue (UGX)'], ['name' => 'orders', 'label' => 'Orders']]"
                    :rows="$byPayment"
                    emptyHeading="No payment method data"
                />
                <x-reports.report-table
                    :columns="[['name' => 'status', 'label' => 'Order Status'], ['name' => 'revenue', 'label' => 'Revenue (UGX)'], ['name' => 'orders', 'label' => 'Orders']]"
                    :rows="$byStatus"
                    emptyHeading="No order status data"
                />
            </div>
        </section>
    </div>
</x-filament-panels::page>
