<?php

namespace App\Filament\Pages\Reports;

use Filament\Forms\Components\Select;

class ApiAnalyticsReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationLabel = 'API Analytics';

    protected static ?int $navigationSort = 70;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.reports.api-analytics-report';

    public function getTitle(): string
    {
        return 'API Analytics';
    }

    protected function getFilterFormSchema(): array
    {
        return [
            ...parent::getFilterFormSchema(),

            Select::make('days')
                ->label('Period')
                ->options([
                    7 => 'Last 7 Days',
                    14 => 'Last 14 Days',
                    30 => 'Last 30 Days',
                    60 => 'Last 60 Days',
                    90 => 'Last 90 Days',
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

    public function getDays(): int
    {
        return (int) $this->getFilterValue('days', 30);
    }

    public function getApiAnalytics(): array
    {
        return $this->apiAnalyticsService->summary($this->getDays());
    }

    protected function getReportSlug(): string
    {
        return 'api-analytics';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'method', 'label' => 'Method'],
            ['name' => 'path', 'label' => 'Path'],
            ['name' => 'count', 'label' => 'Requests'],
            ['name' => 'avg_duration_ms', 'label' => 'Avg Duration (ms)'],
            ['name' => 'errors', 'label' => 'Errors'],
        ];
    }

    protected function getExportRows(): array
    {
        return $this->getApiAnalytics()['top_endpoints'] ?? [];
    }
}
