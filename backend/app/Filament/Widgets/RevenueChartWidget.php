<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Revenue Trend';

    protected static ?string $description = 'Paid revenue over the last 30 days';

    protected static ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $end = now()->endOfDay();
        $start = now()->subDays(29)->startOfDay();
        $cacheKey = 'admin.charts.revenue.' . $start->toDateString() . '.' . $end->toDateString();

        $data = Cache::remember($cacheKey, 3600, function () use ($start, $end): array {
            $labels = [];
            $values = [];

            $current = $start->copy();
            while ($current <= $end) {
                $labels[] = $current->format('M d');
                $values[] = (float) Order::paidRevenueBetween(
                    $current->copy()->startOfDay(),
                    $current->copy()->endOfDay()
                );
                $current->addDay();
            }

            return ['labels' => $labels, 'values' => $values];
        });

        return [
            'labels' => $data['labels'],
            'datasets' => [
                [
                    'label' => 'Revenue (UGX)',
                    'data' => $data['values'],
                    'fill' => true,
                    'tension' => 0.4,
                    'borderColor' => '#0d3b66',
                    'backgroundColor' => 'rgba(13, 59, 102, 0.1)',
                    'pointBackgroundColor' => '#0d3b66',
                    'pointBorderColor' => '#ffffff',
                    'pointHoverBackgroundColor' => '#70c050',
                    'pointHoverBorderColor' => '#ffffff',
                ],
            ],
        ];
    }

    protected function getOptions(): array | RawJs | null
    {
        return RawJs::make(<<<'JS'
        {
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => 'UGX ' + context.parsed.y.toLocaleString()
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => 'UGX ' + (value / 1000) + 'k'
                    }
                },
                x: {
                    grid: { display: false }
                }
            },
            maintainAspectRatio: false,
            responsive: true
        }
        JS);
    }
}
