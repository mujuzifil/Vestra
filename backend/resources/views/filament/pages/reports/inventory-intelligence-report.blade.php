<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Inventory turnover, valuation by category, warehouse utilization, and dead stock.
            </p>
        </div>

        <x-filament::section icon="heroicon-o-funnel" heading="Filters">
            {{ $this->form }}
        </x-filament::section>

        @php
            $turnover = $this->getInventoryTurnover();
            $valuation = $this->getStockValuationByCategory();
            $warehouses = $this->getWarehouseUtilization();
            $deadStock = $this->getDeadStock();
        @endphp

        <section aria-labelledby="inventory-valuation-heading">
            <h2 id="inventory-valuation-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Stock Valuation by Category</h2>
            @php
                $valuationLabels = array_column($valuation, 'category');
                $valuationValues = array_column($valuation, 'value');
                $valuationColors = ['#0d3b66', '#70c050', '#d4af37', '#dc2626', '#4a90d9', '#64748b', '#8b5cf6', '#f59e0b'];
            @endphp
            @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                'chartType' => 'doughnut',
                'chartHeading' => 'Value Distribution',
                'chartLabels' => $valuationLabels,
                'chartDatasets' => [[
                    'label' => 'Value (UGX)',
                    'data' => $valuationValues,
                    'backgroundColor' => array_slice($valuationColors, 0, count($valuationValues)),
                    'borderWidth' => 0,
                ]],
            ])
        </section>

        <section aria-labelledby="inventory-tables-heading">
            <h2 id="inventory-tables-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Detailed Data</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-reports.report-table
                    :columns="[['name' => 'name', 'label' => 'Product'], ['name' => 'sku', 'label' => 'SKU'], ['name' => 'stock_quantity', 'label' => 'Stock'], ['name' => 'sold', 'label' => 'Sold'], ['name' => 'turnover_ratio', 'label' => 'Turnover']]"
                    :rows="$turnover"
                    emptyHeading="No turnover data"
                />
                <x-reports.report-table
                    :columns="[['name' => 'warehouse', 'label' => 'Warehouse'], ['name' => 'total_quantity', 'label' => 'Total'], ['name' => 'total_reserved', 'label' => 'Reserved'], ['name' => 'available', 'label' => 'Available']]"
                    :rows="$warehouses"
                    emptyHeading="No warehouse data"
                />
                <x-reports.report-table
                    :columns="[['name' => 'name', 'label' => 'Product'], ['name' => 'sku', 'label' => 'SKU'], ['name' => 'stock_quantity', 'label' => 'Stock'], ['name' => 'stock_value', 'label' => 'Value (UGX)']]"
                    :rows="$deadStock"
                    emptyHeading="No dead stock"
                />
            </div>
        </section>
    </div>
</x-filament-panels::page>
