<?php

namespace App\Filament\Widgets;

use App\Models\QuoteRequest;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;

class SalesOverviewChartWidget extends ChartWidget
{
    protected static bool $isLazy = true;

    protected static ?string $heading = 'Sales Overview';

    protected static ?string $description = 'Estimated quote value by day';

    protected static ?string $maxHeight = '320px';

    public ?string $period = 'this-week';

    #[On('dashboard-range-changed')]
    public function updatePeriod(string $range): void
    {
        if (in_array($range, ['this-week', 'this-month', 'last-30-days'], true)) {
            $this->period = $range;
        }
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        [$start, $end, $labelFormat] = $this->getPeriodBounds();

        $cacheKey = 'admin.charts.sales_overview.' . $this->period . '.' . $start->toDateString() . '.' . $end->toDateString();

        $data = Cache::remember($cacheKey, 3600, function () use ($start, $end, $labelFormat): array {
            $labels = [];
            $values = [];

            $current = $start->copy();
            while ($current <= $end) {
                $labels[] = $current->format($labelFormat);
                $values[] = (float) QuoteRequest::query()
                    ->whereDate('created_at', $current)
                    ->sum('estimated_value');
                $current->addDay();
            }

            return ['labels' => $labels, 'values' => $values];
        });

        return [
            'labels' => $data['labels'],
            'datasets' => [
                [
                    'label' => 'Quote Value (UGX)',
                    'data' => $data['values'],
                    'fill' => true,
                    'tension' => 0.4,
                    'borderColor' => '#0d3b66',
                    'backgroundColor' => 'rgba(13, 59, 102, 0.08)',
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
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#f8fafc',
                    bodyColor: '#f8fafc',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: (context) => 'UGX ' + context.parsed.y.toLocaleString()
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: {
                        color: 'rgba(226, 232, 240, 0.6)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11, family: "'Poppins', sans-serif" },
                        callback: (value) => 'UGX ' + (value / 1000) + 'k'
                    }
                },
                x: {
                    border: { display: false },
                    grid: { display: false },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11, family: "'Poppins', sans-serif" }
                    }
                }
            },
            maintainAspectRatio: false,
            responsive: true,
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
        JS);
    }

    /**
     * @return array{\Carbon\Carbon, \Carbon\Carbon, string}
     */
    private function getPeriodBounds(): array
    {
        return match ($this->period) {
            'this-week' => [now()->startOfWeek(), now()->endOfWeek(), 'M d'],
            'this-month' => [now()->startOfMonth(), now()->endOfMonth(), 'M d'],
            'last-30-days' => [now()->subDays(29)->startOfDay(), now()->endOfDay(), 'M d'],
            default => [now()->startOfWeek(), now()->endOfWeek(), 'M d'],
        };
    }
}
