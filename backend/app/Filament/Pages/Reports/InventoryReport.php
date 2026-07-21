<?php

namespace App\Filament\Pages\Reports;

class InventoryReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Inventory';

    protected static ?int $navigationSort = 40;

    protected static string $view = 'filament.pages.reports.inventory-report';

    public function getTitle(): string
    {
        return 'Inventory Analytics';
    }

    public function getInventorySummary(): array
    {
        return $this->reportService->inventorySummary();
    }

    public function getLowStock(): array
    {
        return $this->reportService->lowStock(10, 10);
    }

    public function getOutOfStock(): array
    {
        return $this->reportService->outOfStock(10);
    }

    public function getFastMovingProducts(): array
    {
        return $this->reportService->fastMovingProducts($this->getStartDate(), $this->getEndDate(), 10);
    }

    public function getSlowMovingProducts(): array
    {
        return $this->reportService->slowMovingProducts($this->getStartDate(), $this->getEndDate(), 10);
    }

    public function getProductsNeverOrdered(): array
    {
        return $this->reportService->productsNeverOrdered(10);
    }

    protected function getReportSlug(): string
    {
        return 'inventory';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'name', 'label' => 'Product'],
            ['name' => 'sku', 'label' => 'SKU'],
            ['name' => 'category', 'label' => 'Category'],
            ['name' => 'stock_quantity', 'label' => 'Stock'],
            ['name' => 'price', 'label' => 'Price (UGX)'],
        ];
    }

    protected function getExportRows(): array
    {
        return $this->getLowStock();
    }
}
