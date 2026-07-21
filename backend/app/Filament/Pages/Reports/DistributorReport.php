<?php

namespace App\Filament\Pages\Reports;

class DistributorReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Distributors';

    protected static ?int $navigationSort = 60;

    protected static string $view = 'filament.pages.reports.distributor-report';

    public function getTitle(): string
    {
        return 'Distributor Analytics';
    }

    public function getDistributorSummary(): array
    {
        return $this->reportService->distributorSummary();
    }

    public function getApplicationsByStatus(): array
    {
        return $this->reportService->applicationsByStatus();
    }

    public function getApplicationsByCountry(): array
    {
        return $this->reportService->applicationsByCountry(10);
    }

    public function getApplicationsByRegion(): array
    {
        return $this->reportService->applicationsByRegion(10);
    }

    public function getDistributorTrend(): array
    {
        return $this->reportService->distributorTrend($this->getStartDate(), $this->getEndDate());
    }

    protected function getReportSlug(): string
    {
        return 'distributors';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'country', 'label' => 'Country'],
            ['name' => 'count', 'label' => 'Applications'],
        ];
    }

    protected function getExportRows(): array
    {
        return $this->getApplicationsByCountry();
    }
}
