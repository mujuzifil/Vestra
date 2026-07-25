<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Queue health, failed jobs, scheduler status, storage, cache, and notification delivery.
            </p>
        </div>

        @php
            $queue = $this->getQueueHealth();
            $failed = $this->getRecentFailedJobs();
            $scheduler = $this->getSchedulerStatus();
            $storage = $this->getStorageStatus();
            $cache = $this->getCacheStatus();
            $notifications = $this->getNotificationDeliveryMetrics();
        @endphp

        <section aria-labelledby="ops-kpis-heading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <h2 id="ops-kpis-heading" class="sr-only">Operational KPIs</h2>
            <x-reports.report-kpi-card label="Queue Status" :value="$queue['status']" icon="heroicon-o-server-stack" :color="$queue['status'] === 'healthy' ? 'success' : 'warning'" />
            <x-reports.report-kpi-card label="Failed Jobs (24h)" :value="number_format($queue['failed_jobs_last_24h'])" icon="heroicon-o-x-circle" color="danger" />
            <x-reports.report-kpi-card label="Pending Jobs" :value="number_format($queue['pending_jobs'])" icon="heroicon-o-queue-list" color="info" />
            <x-reports.report-kpi-card label="Notification Read Rate" :value="number_format($notifications['read_rate'], 1) . '%'" icon="heroicon-o-envelope-open" color="primary" />
        </section>

        <section aria-labelledby="ops-status-heading" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <x-filament::section heading="Scheduler" icon="heroicon-o-calendar">
                <p><strong>Last run:</strong> {{ $scheduler['last_run_at'] ?? 'Never' }}</p>
                <p><strong>Healthy:</strong> {{ $scheduler['healthy'] ? 'Yes' : 'No' }}</p>
            </x-filament::section>

            <x-filament::section heading="Storage" icon="heroicon-o-hard-drive">
                <p><strong>Path:</strong> {{ $storage['path'] }}</p>
                <p><strong>Used:</strong> {{ number_format($storage['used_percentage'], 1) }}%</p>
                <p><strong>Free:</strong> {{ number_format($storage['free_bytes'] / 1024 / 1024 / 1024, 2) }} GB</p>
            </x-filament::section>

            <x-filament::section heading="Cache" icon="heroicon-o-bolt">
                <p><strong>Driver:</strong> {{ $cache['driver'] }}</p>
                <p><strong>Prefix:</strong> {{ $cache['prefix'] }}</p>
                <p><strong>Reachable:</strong> {{ $cache['reachable'] ? 'Yes' : 'No' }}</p>
            </x-filament::section>
        </section>

        <section aria-labelledby="failed-jobs-heading">
            <h2 id="failed-jobs-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Recent Failed Jobs</h2>
            <x-reports.report-table
                :columns="[['name' => 'connection', 'label' => 'Connection'], ['name' => 'queue', 'label' => 'Queue'], ['name' => 'exception', 'label' => 'Exception'], ['name' => 'failed_at', 'label' => 'Failed At']]"
                :rows="$failed"
                emptyHeading="No failed jobs"
            />
        </section>
    </div>
</x-filament-panels::page>
