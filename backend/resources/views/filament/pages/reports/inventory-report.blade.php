<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Monitor stock levels, inventory value, and product movement.
            </p>
        </div>

        <x-filament::section icon="heroicon-o-funnel" heading="Filters">
            {{ $this->form }}
        </x-filament::section>

        @php
            $summary = $this->getInventorySummary();
            $lowStock = $this->getLowStock();
            $outOfStock = $this->getOutOfStock();
            $fastMoving = $this->getFastMovingProducts();
            $slowMoving = $this->getSlowMovingProducts();
            $neverOrdered = $this->getProductsNeverOrdered();
        @endphp

        <section aria-labelledby="inventory-kpis-heading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <h2 id="inventory-kpis-heading" class="sr-only">Inventory KPIs</h2>
            <x-reports.report-kpi-card label="Inventory Value" :value="'UGX ' . number_format($summary['total_value'])" icon="heroicon-o-banknotes" color="primary" />
            <x-reports.report-kpi-card label="Total Units" :value="number_format($summary['total_units'])" icon="heroicon-o-cube" color="info" />
            <x-reports.report-kpi-card label="Products" :value="number_format($summary['product_count'])" icon="heroicon-o-rectangle-stack" color="success" />
            <x-reports.report-kpi-card label="Low Stock" :value="number_format($summary['low_stock'])" icon="heroicon-o-exclamation-triangle" color="warning" />
            <x-reports.report-kpi-card label="Out of Stock" :value="number_format($summary['out_of_stock'])" icon="heroicon-o-x-circle" color="danger" />
        </section>

        <section aria-labelledby="inventory-breakdown-heading">
            <h2 id="inventory-breakdown-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Inventory Breakdown</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                @php
                    $healthLabels = ['In Stock', 'Low Stock', 'Out of Stock'];
                    $healthValues = [
                        max(0, $summary['product_count'] - $summary['low_stock'] - $summary['out_of_stock']),
                        $summary['low_stock'],
                        $summary['out_of_stock'],
                    ];
                @endphp
                @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                    'chartType' => 'doughnut',
                    'chartHeading' => 'Inventory Health',
                    'chartLabels' => $healthLabels,
                    'chartDatasets' => [[
                        'label' => 'Products',
                        'data' => $healthValues,
                        'backgroundColor' => ['#70c050', '#d4af37', '#dc2626'],
                        'borderWidth' => 0,
                    ]],
                ])

                @php
                    $lowNames = array_column($lowStock, 'name');
                    $lowValues = array_column($lowStock, 'stock_quantity');
                @endphp
                @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                    'chartType' => 'bar',
                    'chartHeading' => 'Low Stock Products',
                    'chartLabels' => $lowNames,
                    'chartDatasets' => [[
                        'label' => 'Remaining Stock',
                        'data' => $lowValues,
                        'backgroundColor' => '#d4af37',
                        'borderRadius' => 4,
                    ]],
                ])
            </div>
        </section>

        <section aria-labelledby="inventory-tables-heading">
            <h2 id="inventory-tables-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Detailed Data</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-reports.report-table
                    :columns="[['name' => 'name', 'label' => 'Product'], ['name' => 'sku', 'label' => 'SKU'], ['name' => 'category', 'label' => 'Category'], ['name' => 'stock_quantity', 'label' => 'Stock']]"
                    :rows="$lowStock"
                    emptyHeading="No low stock products"
                />
                <x-reports.report-table
                    :columns="[['name' => 'name', 'label' => 'Product'], ['name' => 'sku', 'label' => 'SKU'], ['name' => 'category', 'label' => 'Category']]"
                    :rows="$outOfStock"
                    emptyHeading="No out of stock products"
                />
                <x-reports.report-table
                    :columns="[['name' => 'name', 'label' => 'Product'], ['name' => 'sku', 'label' => 'SKU'], ['name' => 'total_sold', 'label' => 'Sold']]"
                    :rows="$fastMoving"
                    emptyHeading="No fast moving products"
                />
                <x-reports.report-table
                    :columns="[['name' => 'name', 'label' => 'Product'], ['name' => 'sku', 'label' => 'SKU'], ['name' => 'stock_quantity', 'label' => 'Stock']]"
                    :rows="$slowMoving"
                    emptyHeading="No slow moving products"
                />
            </div>
        </section>
    </div>
</x-filament-panels::page>
