<?php

namespace App\Filament\Pages\Reports;

class DistributorIntelligenceReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Distributor Intelligence';

    protected static ?int $navigationSort = 41;

    protected static string $view = 'filament.pages.reports.distributor-intelligence-report';

    public function getTitle(): string
    {
        return 'Distributor Intelligence';
    }

    public function getDistributorRevenue(): array
    {
        return $this->reportService->distributorRevenue($this->getStartDate(), $this->getEndDate());
    }

    public function getCreditUtilization(): array
    {
        return $this->reportService->distributorCreditUtilization();
    }

    public function getOutstandingBalances(): array
    {
        return $this->reportService->distributorOutstandingBalances();
    }

    public function getPerformanceTrend(): array
    {
        return $this->reportService->distributorPerformanceTrend($this->getStartDate(), $this->getEndDate());
    }

    protected function getReportSlug(): string
    {
        return 'distributor-intelligence';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'company', 'label' => 'Distributor'],
            ['name' => 'revenue', 'label' => 'Revenue (UGX)'],
            ['name' => 'orders', 'label' => 'Orders'],
        ];
    }

    protected function getExportRows(): array
    {
        return $this->getDistributorRevenue();
    }
}
