<?php

namespace App\Filament\Pages\Reports;

class EngagementReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Engagement';

    protected static ?int $navigationSort = 50;

    protected static string $view = 'filament.pages.reports.engagement-report';

    public function getTitle(): string
    {
        return 'Engagement Analytics';
    }

    public function getEngagementSummary(): array
    {
        return $this->reportService->engagementSummary();
    }

    public function getReviewStatistics(): array
    {
        return $this->reportService->reviewStatistics();
    }

    public function getEngagementTrend(): array
    {
        return $this->reportService->engagementTrend($this->getStartDate(), $this->getEndDate());
    }

    protected function getReportSlug(): string
    {
        return 'engagement';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'date', 'label' => 'Date'],
            ['name' => 'reviews', 'label' => 'Reviews'],
            ['name' => 'feedback', 'label' => 'Feedback'],
            ['name' => 'messages', 'label' => 'Messages'],
        ];
    }

    protected function getExportRows(): array
    {
        $trend = $this->getEngagementTrend();
        $rows = [];

        foreach ($trend['labels'] as $index => $label) {
            $rows[] = [
                'date' => $label,
                'reviews' => $trend['reviews'][$index] ?? 0,
                'feedback' => $trend['feedback'][$index] ?? 0,
                'messages' => $trend['messages'][$index] ?? 0,
            ];
        }

        return $rows;
    }
}
