<x-filament-panels::page class="fi-dashboard-page vestra-workspace-dashboard">
    {{-- Header --}}
    <header class="workspace-header">
        <div class="workspace-header-content">
            <div>
                <h1 class="workspace-title">Workspace Dashboard</h1>
                <p class="workspace-subtitle">
                    Welcome back, {{ auth()->user()?->name ?? 'Admin' }}! Here's what's happening with your business today.
                </p>
            </div>

            <div class="workspace-date-selector">
                <label for="dashboard-date-range" class="sr-only">Date range</label>
                <div class="workspace-date-selector-inner">
                    <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4 text-[var(--text-muted)]" />
                    <select
                        id="dashboard-date-range"
                        wire:change="$dispatch('dashboard-range-changed', { range: $event.target.value })"
                        class="workspace-date-select"
                        aria-label="Select dashboard date range"
                    >
                        <option value="this-week" @selected($this->dateRange === 'this-week')>This Week</option>
                        <option value="this-month" @selected($this->dateRange === 'this-month')>This Month</option>
                        <option value="last-30-days" @selected($this->dateRange === 'last-30-days')>Last 30 Days</option>
                    </select>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4 text-[var(--text-muted)]" />
                </div>
            </div>
        </div>
    </header>

    {{-- KPI Cards --}}
    <section class="workspace-section" aria-labelledby="kpi-heading">
        <h2 id="kpi-heading" class="sr-only">Key Performance Indicators</h2>
        @livewire(\App\Filament\Widgets\KpiCardsWidget::class)
    </section>

    {{-- Main grid: Sales Overview + Recent Activity --}}
    <section class="workspace-section" aria-labelledby="overview-heading">
        <h2 id="overview-heading" class="sr-only">Sales overview and recent activity</h2>
        <div class="workspace-grid workspace-grid-2-1">
            @livewire(\App\Filament\Widgets\SalesOverviewChartWidget::class)
            @livewire(\App\Filament\Widgets\RecentActivityWidget::class)
        </div>
    </section>

    {{-- Bottom grid: Tasks, Notifications, Calendar --}}
    <section class="workspace-section" aria-labelledby="workspace-tools-heading">
        <h2 id="workspace-tools-heading" class="sr-only">Tasks, notifications, and calendar</h2>
        <div class="workspace-grid workspace-grid-3">
            @livewire(\App\Filament\Widgets\MyTasksWidget::class)
            @livewire(\App\Filament\Widgets\NotificationsWidget::class)
            @livewire(\App\Filament\Widgets\UpcomingEventsWidget::class)
        </div>
    </section>
</x-filament-panels::page>
