<?php

namespace App\Filament\Widgets;

use App\Services\ForecastingService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class ForecastWidget extends ChartWidget
{
    protected static ?string $heading = 'Revenue Forecast';

    protected static ?string $description = 'Projected revenue for the next 30 days';

    protected static ?string $maxHeight = '240px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $cacheKey = 'admin.widgets.forecast.revenue';

        $data = Cache::remember($cacheKey, 3600, function (): array {
            $forecast = app(ForecastingService::class)->revenueForecast(30);

            return [
                'labels' => $forecast['labels'],
                'historical' => $forecast['historical'],
                'forecast' => $forecast['forecast'],
            ];
        });

        return [
            'labels' => $data['labels'],
            'datasets' => [
                [
                    'label' => 'Historical',
                    'data' => $data['historical'],
                    'borderColor' => '#0d3b66',
                    'backgroundColor' => 'rgba(13, 59, 102, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 0,
                ],
                [
                    'label' => 'Forecast',
                    'data' => $data['forecast'],
                    'borderColor' => '#70c050',
                    'backgroundColor' => 'rgba(112, 192, 80, 0.1)',
                    'borderDash' => [5, 5],
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 0,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true],
                'x' => ['grid' => ['display' => false]],
            ],
            'maintainAspectRatio' => false,
            'responsive' => true,
        ];
    }
}
