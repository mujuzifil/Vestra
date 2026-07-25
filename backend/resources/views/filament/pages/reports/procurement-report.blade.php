<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Monitor purchase orders, suppliers, and committed spend.
            </p>
        </div>

        <x-filament::section icon="heroicon-o-funnel" heading="Filters">
            {{ $this->form }}
        </x-filament::section>

        @php
            $openOrders = $this->getOpenOrdersCount();
            $committedSpend = $this->getTotalCommittedSpend();
            $supplierCount = $this->getSupplierCount();
            $recentOrders = $this->getRecentPurchaseOrders();
        @endphp

        <section aria-labelledby="procurement-kpis-heading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <h2 id="procurement-kpis-heading" class="sr-only">Procurement KPIs</h2>
            <x-reports.report-kpi-card label="Open Orders" :value="number_format($openOrders)" icon="heroicon-o-clipboard-document-list" color="warning" />
            <x-reports.report-kpi-card label="Committed Spend" :value="'UGX ' . number_format($committedSpend)" icon="heroicon-o-banknotes" color="primary" />
            <x-reports.report-kpi-card label="Active Suppliers" :value="number_format($supplierCount)" icon="heroicon-o-truck" color="info" />
        </section>

        <section aria-labelledby="procurement-orders-heading">
            <h2 id="procurement-orders-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Recent Purchase Orders</h2>
            <x-reports.report-table
                :columns="[['name' => 'po_number', 'label' => 'PO Number'], ['name' => 'supplier', 'label' => 'Supplier'], ['name' => 'status', 'label' => 'Status'], ['name' => 'total', 'label' => 'Total (UGX)'], ['name' => 'expected_at', 'label' => 'Expected']]"
                :rows="$recentOrders"
                emptyHeading="No purchase orders found"
            />
        </section>
    </div>
</x-filament-panels::page>
