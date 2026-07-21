<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class OrderStatusChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Orders by Status';

    protected static ?string $description = 'Current order distribution';

    protected static ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Cache::remember('admin.charts.order_status', 300, function (): array {
            return Order::countByStatus();
        });

        $labels = [];
        $data = [];
        $colors = [];

        foreach (OrderStatus::cases() as $case) {
            $labels[] = $case->label();
            $data[] = $counts[$case->value];
            $colors[] = $this->statusColor($case);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                    'hoverOffset' => 4,
                ],
            ],
        ];
    }

    protected function getOptions(): array | RawJs | null
    {
        return RawJs::make(<<<'JS'
        {
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: (context) => context.label + ': ' + context.parsed + ' orders'
                    }
                }
            },
            cutout: '65%',
            maintainAspectRatio: false,
            responsive: true
        }
        JS);
    }

    private function statusColor(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::PENDING => '#d4af37',
            OrderStatus::PAID => '#70c050',
            OrderStatus::PROCESSING => '#0d3b66',
            OrderStatus::PACKED => '#4a90d9',
            OrderStatus::SHIPPED => '#4a90d9',
            OrderStatus::DELIVERED => '#5aa33d',
            OrderStatus::CANCELLED => '#dc2626',
            OrderStatus::REFUNDED => '#94a3b8',
        };
    }
}
