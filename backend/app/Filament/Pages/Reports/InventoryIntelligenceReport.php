<?php

namespace App\Filament\Pages\Reports;

class InventoryIntelligenceReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';

    protected static ?string $navigationLabel = 'Inventory Intelligence';

    protected static ?int $navigationSort = 51;

    protected static string $view = 'filament.pages.reports.inventory-intelligence-report';

    public function getTitle(): string
    {
        return 'Inventory Intelligence';
    }

    public function getInventoryTurnover(): array
    {
        return $this->reportService->inventoryTurnover($this->getStartDate(), $this->getEndDate());
    }

    public function getStockValuationByCategory(): array
    {
        return $this->reportService->stockValuationByCategory();
    }

    public function getWarehouseUtilization(): array
    {
        return $this->reportService->warehouseUtilization();
    }

    public function getDeadStock(): array
    {
        return $this->reportService->deadStock(90, 20);
    }

    protected function getReportSlug(): string
    {
        return 'inventory-intelligence';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'category', 'label' => 'Category'],
            ['name' => 'value', 'label' => 'Stock Value (UGX)'],
            ['name' => 'units', 'label' => 'Units'],
        ];
    }

    protected function getExportRows(): array
    {
        return $this->getStockValuationByCategory();
    }
}
