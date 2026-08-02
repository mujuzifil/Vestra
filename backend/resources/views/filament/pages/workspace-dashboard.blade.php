@php
$data = $this->getWorkspaceData();
$kpiCards = $data->getKpiCards();
$chartData = $data->getSalesOverviewData($this->dateRange);
$activities = $data->getRecentActivities();
$notifications = $data->getNotifications();
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
                <a href="{{ url('/quote-requests') }}" class="vestra-button vestra-button--primary">View Quotes</a>
                <a href="{{ url('/distributor-requests') }}" class="vestra-button vestra-button--secondary">Applications</a>
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
                    <a href="{{ url('/admin/audit-logs') }}" class="vestra-card-link">View all</a>
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

    {{-- Tasks / Notifications / Calendar --}}
    <section class="vestra-workspace__section" aria-label="Tasks, notifications, and calendar">
        <div class="vestra-grid vestra-grid--3">
            <div class="vestra-card">
                <div class="vestra-card-header">
                    <h3 class="vestra-card-title">My Tasks</h3>
                    <a href="{{ url('/tasks') }}" class="vestra-card-link">View all</a>
                </div>
                <x-admin.empty-state
                    icon="heroicon-o-check-circle"
                    title="No active tasks"
                    description="Tasks assigned to you will appear here once the task management module is enabled."
                />
            </div>

            <div class="vestra-card">
                <div class="vestra-card-header">
                    <div class="flex items-center gap-2">
                        <h3 class="vestra-card-title">Notifications</h3>
                        @if ($notifications['unread_count'] > 0)
                            <span class="vestra-badge vestra-badge--danger">{{ $notifications['unread_count'] }}</span>
                        @endif
                    </div>
                    <a href="{{ url('/admin/notification-dashboard') }}" class="vestra-card-link">View all</a>
                </div>

                <div class="vestra-notification-list">
                    @forelse ($notifications['items'] as $notification)
                        <x-admin.notification-item
                            :icon="$notification['icon']"
                            :title="$notification['title']"
                            :body="$notification['body']"
                            :time="$notification['time']"
                            :read="$notification['read']"
                        />
                    @empty
                        <x-admin.empty-state
                            icon="heroicon-o-bell-slash"
                            title="No notifications"
                            description="You're all caught up."
                        />
                    @endforelse
                </div>
            </div>

            <div class="vestra-card">
                <div class="vestra-card-header">
                    <h3 class="vestra-card-title">Calendar</h3>
                    <span class="vestra-card-link text-[var(--text-muted)]">View calendar</span>
                </div>
                <x-admin.empty-state
                    icon="heroicon-o-calendar"
                    title="No upcoming events"
                    description="Calendar events will appear here once the events module is enabled."
                />
            </div>
        </div>
    </section>
</div>

@vite('resources/js/admin/dashboard-chart.js')
