<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Track orders, best sellers, category performance, and cancellations.
            </p>
        </div>

        <x-filament::section icon="heroicon-o-funnel" heading="Filters">
            {{ $this->form }}
        </x-filament::section>

        @php
            $summary = $this->getSalesSummary();
            $ordersTrend = $this->getOrdersTrend();
            $bestSellers = $this->getBestSellers();
            $worstPerformers = $this->getWorstPerformers();
            $byCategory = $this->getSalesByCategory();
            $cancelled = $this->getCancelledOrders();
        @endphp

        <section aria-labelledby="sales-kpis-heading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <h2 id="sales-kpis-heading" class="sr-only">Sales KPIs</h2>
            <x-reports.report-kpi-card label="Paid Orders" :value="number_format($summary['paid_orders'])" icon="heroicon-o-shopping-bag" color="primary" />
            <x-reports.report-kpi-card label="Total Orders" :value="number_format($summary['total_orders'])" icon="heroicon-o-clipboard-document-list" color="info" />
            <x-reports.report-kpi-card label="Units Sold" :value="number_format($summary['units_sold'])" icon="heroicon-o-cube" color="success" />
            <x-reports.report-kpi-card label="Cancelled" :value="number_format($summary['cancelled_orders'])" icon="heroicon-o-x-circle" color="danger" />
            <x-reports.report-kpi-card label="Refunded" :value="number_format($summary['refunded_orders'])" icon="heroicon-o-arrow-uturn-left" color="warning" />
        </section>

        <section aria-labelledby="orders-trend-heading">
            <h2 id="orders-trend-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Orders Trend</h2>
            @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                'chartType' => 'line',
                'chartHeading' => 'Orders Over Time',
                'chartLabels' => $ordersTrend['labels'],
                'chartDatasets' => [[
                    'label' => 'Orders',
                    'data' => $ordersTrend['orders'],
                    'fill' => true,
                    'tension' => 0.4,
                    'borderColor' => '#0d3b66',
                    'backgroundColor' => 'rgba(13, 59, 102, 0.1)',
                    'pointBackgroundColor' => '#0d3b66',
                ]],
            ])
        </section>

        <section aria-labelledby="sales-breakdown-heading">
            <h2 id="sales-breakdown-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Sales Breakdown</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                @php
                    $categoryLabels = array_column($byCategory, 'category');
                    $categoryValues = array_column($byCategory, 'total_revenue');
                    $categoryColors = ['#0d3b66', '#70c050', '#d4af37', '#dc2626', '#4a90d9', '#64748b'];
                @endphp
                @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                    'chartType' => 'doughnut',
                    'chartHeading' => 'Revenue by Category',
                    'chartLabels' => $categoryLabels,
                    'chartDatasets' => [[
                        'label' => 'Revenue',
                        'data' => $categoryValues,
                        'backgroundColor' => array_slice($categoryColors, 0, count($categoryValues)),
                        'borderWidth' => 0,
                    ]],
                ])

                @php
                    $sellerNames = array_column($bestSellers, 'product_name');
                    $sellerValues = array_column($bestSellers, 'total_sold');
                @endphp
                @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                    'chartType' => 'bar',
                    'chartHeading' => 'Top 10 Best Sellers',
                    'chartLabels' => $sellerNames,
                    'chartDatasets' => [[
                        'label' => 'Units Sold',
                        'data' => $sellerValues,
                        'backgroundColor' => '#70c050',
                        'borderRadius' => 4,
                    ]],
                ])
            </div>
        </section>

        <section aria-labelledby="sales-tables-heading">
            <h2 id="sales-tables-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Detailed Data</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-reports.report-table
                    :columns="[['name' => 'product_name', 'label' => 'Product'], ['name' => 'product_sku', 'label' => 'SKU'], ['name' => 'total_sold', 'label' => 'Sold'], ['name' => 'total_revenue', 'label' => 'Revenue (UGX)']]"
                    :rows="$bestSellers"
                    emptyHeading="No sales data"
                />
                <x-reports.report-table
                    :columns="[['name' => 'product_name', 'label' => 'Product'], ['name' => 'product_sku', 'label' => 'SKU'], ['name' => 'stock_quantity', 'label' => 'Stock']]"
                    :rows="$worstPerformers"
                    emptyHeading="No underperforming products"
                />
                <x-reports.report-table
                    :columns="[['name' => 'invoice', 'label' => 'Invoice'], ['name' => 'customer', 'label' => 'Customer'], ['name' => 'total', 'label' => 'Total (UGX)'], ['name' => 'created_at', 'label' => 'Date']]"
                    :rows="$cancelled"
                    emptyHeading="No cancelled orders"
                />
            </div>
        </section>
    </div>
</x-filament-panels::page>
