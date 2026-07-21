<x-filament-panels::page class="fi-dashboard-page vestra-dashboard">
    @if (method_exists($this, 'filtersForm'))
        {{ $this->filtersForm }}
    @endif

    <div class="dashboard-stack">
        {{-- Executive KPIs --}}
        <section class="dashboard-section" aria-labelledby="executive-kpis-heading">
            <h2 id="executive-kpis-heading" class="sr-only">Executive KPIs</h2>
            @livewire(\App\Filament\Widgets\ExecutiveKpiWidget::class)
        </section>

        {{-- Operational KPIs --}}
        <section class="dashboard-section" aria-labelledby="operational-kpis-heading">
            <h2 id="operational-kpis-heading" class="sr-only">Operational KPIs</h2>
            @livewire(\App\Filament\Widgets\OperationalKpiWidget::class)
        </section>

        {{-- Quick Actions --}}
        <section class="dashboard-section" aria-labelledby="quick-actions-heading">
            <h2 id="quick-actions-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Quick Actions</h2>
            @livewire(\App\Filament\Widgets\QuickActionsWidget::class)
        </section>

        {{-- Charts --}}
        <section class="dashboard-section" aria-labelledby="charts-heading">
            <h2 id="charts-heading" class="sr-only">Charts</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                @livewire(\App\Filament\Widgets\RevenueChartWidget::class)
                @livewire(\App\Filament\Widgets\OrderStatusChartWidget::class)
            </div>
        </section>

        {{-- Table Widgets --}}
        <section class="dashboard-section" aria-labelledby="tables-heading">
            <h2 id="tables-heading" class="sr-only">Recent Orders and Low Stock</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                @livewire(\App\Filament\Widgets\RecentOrdersWidget::class)
                @livewire(\App\Filament\Widgets\LowStockWidget::class)
            </div>
        </section>

        {{-- Alerts & Activity --}}
        <section class="dashboard-section" aria-labelledby="activity-heading">
            <h2 id="activity-heading" class="sr-only">Alerts and Recent Activity</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                @livewire(\App\Filament\Widgets\AlertsWidget::class)
                @livewire(\App\Filament\Widgets\RecentActivityWidget::class)
            </div>
        </section>
    </div>
</x-filament-panels::page>
