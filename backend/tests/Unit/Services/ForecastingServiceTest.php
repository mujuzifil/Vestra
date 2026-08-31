<?php

namespace Tests\Unit\Services;

use App\Services\ForecastingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastingServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_revenue_forecast_returns_historical_and_forecast_arrays(): void
    {
        $service = new ForecastingService();
        $forecast = $service->revenueForecast(7);

        $this->assertArrayHasKey('labels', $forecast);
        $this->assertArrayHasKey('historical', $forecast);
        $this->assertArrayHasKey('forecast', $forecast);
        $this->assertArrayHasKey('trend', $forecast);
        $this->assertCount(7, array_filter($forecast['forecast'], fn ($value) => $value !== null));
    }

    public function test_order_forecast_returns_non_negative_values(): void
    {
        $service = new ForecastingService();
        $forecast = $service->orderForecast(14);

        foreach ($forecast['forecast'] as $value) {
            $this->assertGreaterThanOrEqual(0, $value);
        }
    }

    public function test_inventory_forecast_returns_at_risk_summary(): void
    {
        $service = new ForecastingService();
        $forecast = $service->inventoryForecast(30);

        $this->assertArrayHasKey('forecast_days', $forecast);
        $this->assertArrayHasKey('at_risk_products', $forecast);
        $this->assertArrayHasKey('total_at_risk', $forecast);
        $this->assertEquals(30, $forecast['forecast_days']);
    }

    public function test_customer_growth_forecast_returns_monthly_projection(): void
    {
        $service = new ForecastingService();
        $forecast = $service->customerGrowthForecast(3);

        $this->assertArrayHasKey('labels', $forecast);
        $this->assertArrayHasKey('historical', $forecast);
        $this->assertArrayHasKey('forecast', $forecast);
    }
}
