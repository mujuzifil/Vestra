<?php

namespace App\Filament\Pages\Reports;

use Filament\Forms\Components\Select;

class ForecastReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?string $navigationLabel = 'Forecasting';

    protected static ?int $navigationSort = 60;

    protected static string $view = 'filament.pages.reports.forecast-report';

    public function getTitle(): string
    {
        return 'Forecasting & Projections';
    }

    protected function getFilterFormSchema(): array
    {
        return [
            ...parent::getFilterFormSchema(),

            Select::make('forecast_days')
                ->label('Forecast Days')
                ->options([
                    7 => '7 Days',
                    14 => '14 Days',
                    30 => '30 Days',
                    60 => '60 Days',
                    90 => '90 Days',
                ])
                ->default(30)
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

    public function getForecastDays(): int
    {
        return (int) $this->getFilterValue('forecast_days', 30);
    }

    public function getRevenueForecast(): array
    {
        return $this->forecastingService->revenueForecast($this->getForecastDays());
    }

    public function getOrderForecast(): array
    {
        return $this->forecastingService->orderForecast($this->getForecastDays());
    }

    public function getInventoryForecast(): array
    {
        return $this->forecastingService->inventoryForecast($this->getForecastDays());
    }

    protected function getReportSlug(): string
    {
        return 'forecast';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'date', 'label' => 'Date'],
            ['name' => 'forecast', 'label' => 'Forecast'],
        ];
    }

    protected function getExportRows(): array
    {
        $forecast = $this->getRevenueForecast();
        $rows = [];

        foreach ($forecast['labels'] as $index => $label) {
            if ($forecast['forecast'][$index] !== null) {
                $rows[] = [
                    'date' => $label,
                    'forecast' => $forecast['forecast'][$index],
                ];
            }
        }

        return $rows;
    }
}
