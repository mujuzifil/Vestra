<?php

namespace App\Filament\Pages\Reports;

use Filament\Forms\Components\Select;

class CustomerReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Customers';

    protected static ?int $navigationSort = 30;

    protected static string $view = 'filament.pages.reports.customer-report';

    public function getTitle(): string
    {
        return 'Customer Analytics';
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
                ->default('month')
                ->native(false),
        ];
    }

    protected function getFilterFormColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'lg' => 3,
        ];
    }

    public function getCustomerSummary(): array
    {
        return $this->reportService->customerSummary();
    }

    public function getCustomerGrowth(): array
    {
        return $this->reportService->customerGrowth(
            $this->getStartDate(),
            $this->getEndDate(),
            $this->getFilterValue('granularity', 'month')
        );
    }

    public function getTopCustomers(): array
    {
        return $this->reportService->topCustomers($this->getStartDate(), $this->getEndDate(), 10);
    }

    public function getInactiveCustomers(): array
    {
        return $this->reportService->inactiveCustomers(90, 10);
    }

    public function getAverageCustomerValue(): float
    {
        return $this->reportService->averageCustomerValue();
    }

    protected function getReportSlug(): string
    {
        return 'customers';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'name', 'label' => 'Customer'],
            ['name' => 'email', 'label' => 'Email'],
            ['name' => 'orders', 'label' => 'Orders'],
            ['name' => 'period_spend', 'label' => 'Period Spend (UGX)'],
        ];
    }

    protected function getExportRows(): array
    {
        return $this->getTopCustomers();
    }
}
