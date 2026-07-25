<?php

namespace App\Filament\Pages\Reports;

class CustomerIntelligenceReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Customer Intelligence';

    protected static ?int $navigationSort = 31;

    protected static string $view = 'filament.pages.reports.customer-intelligence-report';

    public function getTitle(): string
    {
        return 'Customer Intelligence';
    }

    public function getCustomerSegments(): array
    {
        return $this->reportService->customerSegments();
    }

    public function getCustomerLifetimeValue(): array
    {
        return $this->reportService->customerLifetimeValue(10);
    }

    public function getRetentionRate(): float
    {
        return $this->reportService->customerRetentionRate($this->getStartDate(), $this->getEndDate());
    }

    public function getChurnRate(): float
    {
        return $this->reportService->customerChurnRate(90);
    }

    public function getTopRegions(): array
    {
        return $this->reportService->topCustomerRegions(10);
    }

    protected function getReportSlug(): string
    {
        return 'customer-intelligence';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'segment', 'label' => 'Segment'],
            ['name' => 'count', 'label' => 'Customers'],
        ];
    }

    protected function getExportRows(): array
    {
        return $this->getCustomerSegments();
    }
}
