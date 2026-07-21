<?php

namespace App\Filament\Widgets\Reports;

use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

/**
 * Reusable chart widget for report pages.
 *
 * Accepts chart configuration via public properties so report pages can pass
 * computed datasets without creating a new widget class for every chart.
 */
class InlineReportChartWidget extends ChartWidget
{
    public string $chartType = 'line';

    public ?string $chartHeading = null;

    public ?string $chartDescription = null;

    public array $chartLabels = [];

    public array $chartDatasets = [];

    public array $chartOptions = [];

    public string $currencyPrefix = 'UGX ';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public function getHeading(): string | null
    {
        return $this->chartHeading;
    }

    public function getDescription(): string | null
    {
        return $this->chartDescription;
    }

    protected function getType(): string
    {
        return $this->chartType;
    }

    protected function getData(): array
    {
        return [
            'labels' => $this->chartLabels,
            'datasets' => $this->chartDatasets,
        ];
    }

    protected function getOptions(): array | RawJs | null
    {
        if ($this->chartOptions !== []) {
            return RawJs::make(json_encode($this->chartOptions));
        }

        return RawJs::make(<<<'JS'
        {
            plugins: {
                legend: { display: true, position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            let label = context.dataset.label ? context.dataset.label + ': ' : '';
                            let value = context.parsed.y !== undefined ? context.parsed.y : context.parsed;
                            return label + value.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true },
                x: { grid: { display: false } }
            },
            maintainAspectRatio: false,
            responsive: true
        }
        JS);
    }
}
