<?php

namespace App\Filament\Pages\Reports;

class OperationalMonitoringReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    protected static ?string $navigationLabel = 'Operational Monitoring';

    protected static ?int $navigationSort = 71;

    protected static string $view = 'filament.pages.reports.operational-monitoring-report';

    public function getTitle(): string
    {
        return 'Operational Monitoring';
    }

    public function getQueueHealth(): array
    {
        return $this->reportService->queueHealth();
    }

    public function getRecentFailedJobs(): array
    {
        return $this->reportService->recentFailedJobs(10);
    }

    public function getSchedulerStatus(): array
    {
        return $this->reportService->schedulerStatus();
    }

    public function getStorageStatus(): array
    {
        return $this->reportService->storageStatus();
    }

    public function getCacheStatus(): array
    {
        return $this->reportService->cacheStatus();
    }

    public function getNotificationDeliveryMetrics(): array
    {
        return $this->reportService->notificationDeliveryMetrics(30);
    }

    protected function getReportSlug(): string
    {
        return 'operational-monitoring';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'connection', 'label' => 'Connection'],
            ['name' => 'queue', 'label' => 'Queue'],
            ['name' => 'exception', 'label' => 'Exception'],
            ['name' => 'failed_at', 'label' => 'Failed At'],
        ];
    }

    protected function getExportRows(): array
    {
        return $this->getRecentFailedJobs();
    }
}
