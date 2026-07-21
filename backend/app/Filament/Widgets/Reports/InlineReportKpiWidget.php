<?php

namespace App\Filament\Widgets\Reports;

use Filament\Widgets\StatsOverviewWidget;

/**
 * Reusable KPI widget for report pages.
 *
 * Accepts an array of Filament Stat objects via the `stats` property.
 */
class InlineReportKpiWidget extends StatsOverviewWidget
{
    public array $stats = [];

    protected function getStats(): array
    {
        return $this->stats;
    }
}
