<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Understand customer growth, lifetime value, and engagement patterns.
            </p>
        </div>

        <x-filament::section icon="heroicon-o-funnel" heading="Filters">
            {{ $this->form }}
        </x-filament::section>

        @php
            $summary = $this->getCustomerSummary();
            $growth = $this->getCustomerGrowth();
            $topCustomers = $this->getTopCustomers();
            $inactive = $this->getInactiveCustomers();
        @endphp

        <section aria-labelledby="customer-kpis-heading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <h2 id="customer-kpis-heading" class="sr-only">Customer KPIs</h2>
            <x-reports.report-kpi-card label="Total Customers" :value="number_format($summary['total_customers'])" icon="heroicon-o-users" color="primary" />
            <x-reports.report-kpi-card label="New This Month" :value="number_format($summary['new_this_month'])" icon="heroicon-o-user-plus" color="success" />
            <x-reports.report-kpi-card label="Repeat Customers" :value="number_format($summary['repeat_customers'])" icon="heroicon-o-arrow-path" color="info" />
            <x-reports.report-kpi-card label="Inactive" :value="number_format($summary['inactive_customers'])" icon="heroicon-o-user-minus" color="warning" />
            <x-reports.report-kpi-card label="Avg. Customer Value" :value="'UGX ' . number_format($this->getAverageCustomerValue())" icon="heroicon-o-calculator" color="primary" />
        </section>

        <section aria-labelledby="customer-growth-heading">
            <h2 id="customer-growth-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Customer Growth</h2>
            @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                'chartType' => 'line',
                'chartHeading' => 'New Registrations',
                'chartLabels' => $growth['labels'],
                'chartDatasets' => [[
                    'label' => 'New Customers',
                    'data' => $growth['new_customers'],
                    'fill' => true,
                    'tension' => 0.4,
                    'borderColor' => '#70c050',
                    'backgroundColor' => 'rgba(112, 192, 80, 0.1)',
                    'pointBackgroundColor' => '#70c050',
                ]],
            ])
        </section>

        <section aria-labelledby="customer-tables-heading">
            <h2 id="customer-tables-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Detailed Data</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-reports.report-table
                    :columns="[['name' => 'name', 'label' => 'Customer'], ['name' => 'email', 'label' => 'Email'], ['name' => 'orders', 'label' => 'Orders'], ['name' => 'period_spend', 'label' => 'Period Spend (UGX)']]"
                    :rows="$topCustomers"
                    emptyHeading="No customer data"
                />
                <x-reports.report-table
                    :columns="[['name' => 'name', 'label' => 'Customer'], ['name' => 'email', 'label' => 'Email'], ['name' => 'registered_at', 'label' => 'Registered']]"
                    :rows="$inactive"
                    emptyHeading="No inactive customers"
                />
            </div>
        </section>
    </div>
</x-filament-panels::page>
