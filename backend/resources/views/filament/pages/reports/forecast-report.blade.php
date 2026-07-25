<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Revenue, order, inventory, and growth forecasts based on recent historical data.
            </p>
        </div>

        <x-filament::section icon="heroicon-o-funnel" heading="Filters">
            {{ $this->form }}
        </x-filament::section>

        @php
            $revenue = $this->getRevenueForecast();
            $orders = $this->getOrderForecast();
            $inventory = $this->getInventoryForecast();
        @endphp

        <section aria-labelledby="revenue-forecast-heading">
            <h2 id="revenue-forecast-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Revenue Forecast</h2>
            @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                'chartType' => 'line',
                'chartHeading' => 'Revenue Projection (UGX)',
                'chartLabels' => $revenue['labels'],
                'chartDatasets' => [
                    [
                        'label' => 'Historical',
                        'data' => $revenue['historical'],
                        'borderColor' => '#0d3b66',
                        'backgroundColor' => 'rgba(13, 59, 102, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Forecast',
                        'data' => $revenue['forecast'],
                        'borderColor' => '#70c050',
                        'backgroundColor' => 'rgba(112, 192, 80, 0.1)',
                        'borderDash' => [5, 5],
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                ],
            ])
        </section>

        <section aria-labelledby="order-forecast-heading">
            <h2 id="order-forecast-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Order Forecast</h2>
            @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                'chartType' => 'line',
                'chartHeading' => 'Order Projection',
                'chartLabels' => $orders['labels'],
                'chartDatasets' => [
                    [
                        'label' => 'Historical',
                        'data' => $orders['historical'],
                        'borderColor' => '#0d3b66',
                        'backgroundColor' => 'rgba(13, 59, 102, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Forecast',
                        'data' => $orders['forecast'],
                        'borderColor' => '#70c050',
                        'backgroundColor' => 'rgba(112, 192, 80, 0.1)',
                        'borderDash' => [5, 5],
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                ],
            ])
        </section>

        <section aria-labelledby="inventory-forecast-heading">
            <h2 id="inventory-forecast-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">At-Risk Stockouts ({{ $inventory['forecast_days'] }} Days)</h2>
            <x-reports.report-table
                :columns="[['name' => 'name', 'label' => 'Product'], ['name' => 'sku', 'label' => 'SKU'], ['name' => 'stock_quantity', 'label' => 'Stock'], ['name' => 'sold_30d', 'label' => 'Sold (30d)'], ['name' => 'daily_velocity', 'label' => 'Daily Velocity'], ['name' => 'projected_stockout_days', 'label' => 'Stockout In (Days)']]"
                :rows="$inventory['at_risk_products']"
                emptyHeading="No products projected to stock out"
            />
        </section>
    </div>
</x-filament-panels::page>
