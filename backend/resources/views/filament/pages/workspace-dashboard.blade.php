@php
$data = $this->getWorkspaceData();
$kpiCards = $data->getKpiCards();
$chartData = $data->getSalesOverviewData($this->dateRange);
$activities = $data->getRecentActivities();
@endphp

<div class="vestra-workspace">
    {{-- Hero --}}
    <section class="vestra-workspace__hero">
        <div>
            <h1 class="vestra-workspace__title">Workspace Dashboard</h1>
            <p class="vestra-workspace__welcome">
                Welcome back, {{ filament()->auth()->user()?->name ?? 'Admin' }}! Here's what's happening with your business today.
            </p>
        </div>
        <div class="vestra-workspace__meta">
            <span class="vestra-workspace__date">{{ $this->getHeroDate() }}</span>
            <div class="vestra-workspace__quick-actions">
                <a href="{{ \App\Filament\Pages\Sales\QuotesPage::getUrl() }}" class="vestra-button vestra-button--primary">View Quotes</a>
                <a href="{{ \App\Filament\Pages\Distributors\ApplicationsPage::getUrl() }}" class="vestra-button vestra-button--secondary">Applications</a>
            </div>
        </div>
    </section>

    {{-- KPI Cards --}}
    <section class="vestra-workspace__section" aria-label="Key performance indicators">
        <div class="vestra-kpi-grid">
            @foreach ($kpiCards as $card)
                <x-admin.kpi-card
                    :icon="$card['icon']"
                    :label="$card['label']"
                    :value="$card['value']"
                    :trend="$card['trend']"
                    :trend-label="$card['trend_label']"
                    :trend-positive="$card['trend_positive']"
                    :color="$card['color']"
                />
            @endforeach
        </div>
    </section>

    {{-- Chart + Activity --}}
    <section class="vestra-workspace__section" aria-label="Sales overview and recent activity">
        <div class="vestra-grid vestra-grid--2-1">
            <x-admin.chart-container
                id="sales-overview-chart"
                title="Sales Overview"
                :labels="$chartData['labels']"
                :values="$chartData['values']"
                :empty="empty($chartData['values']) || max($chartData['values']) <= 0"
            />

            <div class="vestra-card vestra-activity-card">
                <div class="vestra-card-header">
                    <h3 class="vestra-card-title">Recent Activity</h3>
                    <a href="{{ \App\Filament\Pages\Workspace\ActivityPage::getUrl() }}" class="vestra-card-link">View all</a>
                </div>

                <div class="vestra-activity-list">
                    @forelse ($activities as $activity)
                        <x-admin.activity-item
                            :icon="$activity['icon']"
                            :color="$activity['color']"
                            :title="$activity['title']"
                            :subtitle="$activity['subtitle']"
                            :time="$activity['time']"
                            :url="$activity['url']"
                        />
                    @empty
                        <x-admin.empty-state
                            icon="heroicon-o-clock"
                            title="No recent activity"
                            description="Actions will appear here as your team works."
                        />
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>

@vite('resources/js/admin/dashboard-chart.js')
