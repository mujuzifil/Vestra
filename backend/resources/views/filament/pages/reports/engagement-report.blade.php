<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Track reviews, customer feedback, contact messages, and moderation workload.
            </p>
        </div>

        <x-filament::section icon="heroicon-o-funnel" heading="Filters">
            {{ $this->form }}
        </x-filament::section>

        @php
            $summary = $this->getEngagementSummary();
            $reviewStats = $this->getReviewStatistics();
            $trend = $this->getEngagementTrend();
        @endphp

        <section aria-labelledby="engagement-kpis-heading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <h2 id="engagement-kpis-heading" class="sr-only">Engagement KPIs</h2>
            <x-reports.report-kpi-card label="Total Reviews" :value="number_format($summary['total_reviews'])" icon="heroicon-o-star" color="primary" />
            <x-reports.report-kpi-card label="Pending Reviews" :value="number_format($summary['pending_reviews'])" icon="heroicon-o-clock" color="warning" />
            <x-reports.report-kpi-card label="Average Rating" :value="$summary['average_rating'] . ' / 5'" icon="heroicon-o-heart" color="success" />
            <x-reports.report-kpi-card label="Unread Messages" :value="number_format($summary['unread_feedback'] + $summary['unread_messages'])" icon="heroicon-o-envelope" color="danger" />
        </section>

        <section aria-labelledby="engagement-trend-heading">
            <h2 id="engagement-trend-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Engagement Trend</h2>
            @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                'chartType' => 'line',
                'chartHeading' => 'Reviews, Feedback & Messages',
                'chartLabels' => $trend['labels'],
                'chartDatasets' => [
                    [
                        'label' => 'Reviews',
                        'data' => $trend['reviews'],
                        'borderColor' => '#0d3b66',
                        'backgroundColor' => 'rgba(13, 59, 102, 0.05)',
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                    [
                        'label' => 'Feedback',
                        'data' => $trend['feedback'],
                        'borderColor' => '#70c050',
                        'backgroundColor' => 'rgba(112, 192, 80, 0.05)',
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                    [
                        'label' => 'Messages',
                        'data' => $trend['messages'],
                        'borderColor' => '#d4af37',
                        'backgroundColor' => 'rgba(212, 175, 55, 0.05)',
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                ],
            ])
        </section>

        <section aria-labelledby="engagement-breakdown-heading">
            <h2 id="engagement-breakdown-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Review Breakdown</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                @php
                    $statusLabels = array_column($reviewStats['by_status'], 'label');
                    $statusValues = array_column($reviewStats['by_status'], 'count');
                    $statusColors = ['#d4af37', '#70c050', '#dc2626'];
                @endphp
                @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                    'chartType' => 'doughnut',
                    'chartHeading' => 'Reviews by Status',
                    'chartLabels' => $statusLabels,
                    'chartDatasets' => [[
                        'label' => 'Reviews',
                        'data' => $statusValues,
                        'backgroundColor' => array_slice($statusColors, 0, count($statusValues)),
                        'borderWidth' => 0,
                    ]],
                ])

                @php
                    $ratingLabels = [];
                    $ratingValues = [];
                    foreach ($reviewStats['by_rating'] as $rating => $count) {
                        $ratingLabels[] = $rating . ' Star' . ($rating > 1 ? 's' : '');
                        $ratingValues[] = $count;
                    }
                @endphp
                @livewire(\App\Filament\Widgets\Reports\InlineReportChartWidget::class, [
                    'chartType' => 'bar',
                    'chartHeading' => 'Reviews by Rating',
                    'chartLabels' => $ratingLabels,
                    'chartDatasets' => [[
                        'label' => 'Reviews',
                        'data' => $ratingValues,
                        'backgroundColor' => '#d4af37',
                        'borderRadius' => 4,
                    ]],
                ])
            </div>
        </section>

        <section aria-labelledby="engagement-tables-heading">
            <h2 id="engagement-tables-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Detailed Data</h2>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <x-reports.report-table
                    :columns="[['name' => 'label', 'label' => 'Status'], ['name' => 'count', 'label' => 'Count']]"
                    :rows="$reviewStats['by_status']"
                    emptyHeading="No review data"
                />
                <x-reports.report-table
                    :columns="[['name' => 'label', 'label' => 'Rating'], ['name' => 'count', 'label' => 'Count']]"
                    :rows="collect($reviewStats['by_rating'])->map(fn ($count, $rating) => ['label' => $rating . ' Star' . ($rating > 1 ? 's' : ''), 'count' => $count])->values()->toArray()"
                    emptyHeading="No rating data"
                />
            </div>
        </section>
    </div>
</x-filament-panels::page>
