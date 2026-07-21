<?php

namespace App\Filament\Pages\Reports;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Support\RawJs;

class RevenueReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Revenue';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.reports.revenue-report';

    public function getTitle(): string
    {
        return 'Revenue Analytics';
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

            Select::make('payment_status')
                ->label('Payment Status')
                ->options([
                    PaymentStatus::PAID->value => PaymentStatus::PAID->label(),
                    PaymentStatus::PENDING->value => PaymentStatus::PENDING->label(),
                    PaymentStatus::REFUNDED->value => PaymentStatus::REFUNDED->label(),
                ])
                ->placeholder('All payment statuses')
                ->native(false),

            Select::make('order_status')
                ->label('Order Status')
                ->options([
                    OrderStatus::PENDING->value => OrderStatus::PENDING->label(),
                    OrderStatus::PROCESSING->value => OrderStatus::PROCESSING->label(),
                    OrderStatus::SHIPPED->value => OrderStatus::SHIPPED->label(),
                    OrderStatus::DELIVERED->value => OrderStatus::DELIVERED->label(),
                    OrderStatus::CANCELLED->value => OrderStatus::CANCELLED->label(),
                    OrderStatus::REFUNDED->value => OrderStatus::REFUNDED->label(),
                ])
                ->placeholder('All order statuses')
                ->native(false),
        ];
    }

    protected function getFilterFormColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'lg' => 5,
        ];
    }

    public function getRevenueSummary(): array
    {
        return $this->reportService->revenueSummary($this->getStartDate(), $this->getEndDate());
    }

    public function getRevenueTrend(): array
    {
        return $this->reportService->revenueTrend(
            $this->getStartDate(),
            $this->getEndDate(),
            $this->getFilterValue('granularity', 'day')
        );
    }

    public function getRevenueByPaymentMethod(): array
    {
        return $this->reportService->revenueByPaymentMethod($this->getStartDate(), $this->getEndDate());
    }

    public function getRevenueByOrderStatus(): array
    {
        return $this->reportService->revenueByOrderStatus($this->getStartDate(), $this->getEndDate());
    }

    public function getChartDatasets(): array
    {
        $trend = $this->getRevenueTrend();

        return [
            [
                'label' => 'Revenue (UGX)',
                'data' => $trend['revenue'],
                'fill' => true,
                'tension' => 0.4,
                'borderColor' => '#0d3b66',
                'backgroundColor' => 'rgba(13, 59, 102, 0.1)',
                'pointBackgroundColor' => '#0d3b66',
                'pointBorderColor' => '#ffffff',
                'pointHoverBackgroundColor' => '#70c050',
                'pointHoverBorderColor' => '#ffffff',
            ],
        ];
    }

    public function getChartOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true],
                'x' => ['grid' => ['display' => false]],
            ],
            'maintainAspectRatio' => false,
            'responsive' => true,
        ];
    }

    protected function getReportSlug(): string
    {
        return 'revenue';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'period', 'label' => 'Period'],
            ['name' => 'revenue', 'label' => 'Revenue (UGX)'],
            ['name' => 'orders', 'label' => 'Orders'],
        ];
    }

    protected function getExportRows(): array
    {
        $trend = $this->getRevenueTrend();
        $rows = [];

        foreach ($trend['labels'] as $index => $label) {
            $rows[] = [
                'period' => $label,
                'revenue' => $trend['revenue'][$index] ?? 0,
                'orders' => $trend['orders'][$index] ?? 0,
            ];
        }

        return $rows;
    }
}
