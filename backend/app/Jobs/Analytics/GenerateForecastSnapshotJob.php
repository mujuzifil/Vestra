<?php

namespace App\Jobs\Analytics;

use App\Models\ReportSnapshot;
use App\Services\ForecastingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateForecastSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ForecastingService $forecastingService): void
    {
        ReportSnapshot::create([
            'type' => 'forecast',
            'period' => now()->toDateString(),
            'data' => [
                'revenue' => $forecastingService->revenueForecast(30),
                'orders' => $forecastingService->orderForecast(30),
                'inventory' => $forecastingService->inventoryForecast(30),
                'customer_growth' => $forecastingService->customerGrowthForecast(3),
                'distributor_growth' => $forecastingService->distributorGrowthForecast(3),
            ],
        ]);
    }
}
