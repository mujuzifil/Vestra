<?php

namespace App\Filament\Pages\Reports;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use Filament\Forms\Components\Select;

class SalesReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Sales';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.reports.sales-report';

    public function getTitle(): string
    {
        return 'Sales Analytics';
    }

    protected function getFilterFormSchema(): array
    {
        return [
            ...parent::getFilterFormSchema(),

            Select::make('granularity')
                ->label('Granularity')
                ->options([
                    'day' => 'Daily',
                    'week' => 'Weekly',
                    'month' => 'Monthly',
                ])
                ->default('day')
                ->native(false),

            Select::make('category_id')
                ->label('Category')
                ->options(fn () => Category::query()->pluck('name', 'id')->toArray())
                ->placeholder('All categories')
                ->native(false)
                ->preload(),
        ];
    }

    protected function getFilterFormColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'lg' => 4,
        ];
    }

    public function getSalesSummary(): array
    {
        return $this->reportService->salesSummary($this->getStartDate(), $this->getEndDate());
    }

    public function getOrdersTrend(): array
    {
        return $this->reportService->ordersTrend(
            $this->getStartDate(),
            $this->getEndDate(),
            $this->getFilterValue('granularity', 'day')
        );
    }

    public function getBestSellers(): array
    {
        return $this->reportService->bestSellers($this->getStartDate(), $this->getEndDate(), 10);
    }

    public function getWorstPerformers(): array
    {
        return $this->reportService->worstPerformers($this->getStartDate(), $this->getEndDate(), 10);
    }

    public function getSalesByCategory(): array
    {
        return $this->reportService->salesByCategory($this->getStartDate(), $this->getEndDate(), 10);
    }

    public function getCancelledOrders(): array
    {
        return $this->reportService->cancelledOrders($this->getStartDate(), $this->getEndDate(), 10);
    }

    protected function getReportSlug(): string
    {
        return 'sales';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'product_name', 'label' => 'Product'],
            ['name' => 'product_sku', 'label' => 'SKU'],
            ['name' => 'total_sold', 'label' => 'Units Sold'],
            ['name' => 'total_revenue', 'label' => 'Revenue (UGX)'],
        ];
    }

    protected function getExportRows(): array
    {
        return $this->getBestSellers();
    }
}
