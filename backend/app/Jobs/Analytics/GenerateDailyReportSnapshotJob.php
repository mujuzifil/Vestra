<?php

namespace App\Jobs\Analytics;

use App\Models\ReportSnapshot;
use App\Services\ForecastingService;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateDailyReportSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ReportService $reportService, ForecastingService $forecastingService): void
    {
        $start = now()->subDay()->startOfDay();
        $end = now()->subDay()->endOfDay();
        $period = $start->toDateString();

        ReportSnapshot::create([
            'type' => 'executive',
            'period' => $period,
            'data' => $reportService->executiveSummary($start, $end),
        ]);

        ReportSnapshot::create([
            'type' => 'sales',
            'period' => $period,
            'data' => $reportService->salesSummary($start, $end),
        ]);

        ReportSnapshot::create([
            'type' => 'inventory',
            'period' => $period,
            'data' => $reportService->inventorySummary(),
        ]);

        ReportSnapshot::create([
            'type' => 'forecast',
            'period' => $period,
            'data' => [
                'revenue' => $forecastingService->revenueForecast(30),
                'orders' => $forecastingService->orderForecast(30),
                'inventory' => $forecastingService->inventoryForecast(30),
            ],
        ]);
    }
}
