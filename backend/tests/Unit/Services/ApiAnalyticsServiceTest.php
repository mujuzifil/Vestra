<?php

namespace Tests\Unit\Services;

use App\Models\ApiRequestLog;
use App\Services\ApiAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_returns_all_metric_groups(): void
    {
        ApiRequestLog::factory()->count(5)->create([
            'path' => '/api/v1/products',
            'method' => 'GET',
            'status_code' => 200,
            'duration_ms' => 120,
            'created_at' => now()->subHour(),
        ]);

        $service = new ApiAnalyticsService();
        $summary = $service->summary(1);

        $this->assertArrayHasKey('volume', $summary);
        $this->assertArrayHasKey('top_endpoints', $summary);
        $this->assertArrayHasKey('error_rate', $summary);
        $this->assertArrayHasKey('average_latency', $summary);
        $this->assertArrayHasKey('auth_failure_rate', $summary);

        $this->assertGreaterThanOrEqual(5, array_sum($summary['volume']['requests']));
        $this->assertNotEmpty($summary['top_endpoints']);
    }

    public function test_top_endpoints_returns_aggregated_data(): void
    {
        ApiRequestLog::factory()->count(3)->create([
            'path' => '/api/v1/orders',
            'method' => 'GET',
            'status_code' => 200,
            'duration_ms' => 100,
        ]);

        $service = new ApiAnalyticsService();
        $endpoints = $service->topEndpoints(30, 10);

        $this->assertNotEmpty($endpoints);
        $this->assertArrayHasKey('method', $endpoints[0]);
        $this->assertArrayHasKey('path', $endpoints[0]);
        $this->assertArrayHasKey('count', $endpoints[0]);
        $this->assertArrayHasKey('avg_duration_ms', $endpoints[0]);
        $this->assertArrayHasKey('errors', $endpoints[0]);
    }

    public function test_error_rate_returns_percentage_values(): void
    {
        ApiRequestLog::factory()->count(2)->create([
            'status_code' => 200,
            'created_at' => now(),
        ]);
        ApiRequestLog::factory()->count(2)->create([
            'status_code' => 500,
            'created_at' => now(),
        ]);

        $service = new ApiAnalyticsService();
        $errorRate = $service->errorRate(1);

        $this->assertArrayHasKey('labels', $errorRate);
        $this->assertArrayHasKey('error_rate', $errorRate);
        $this->assertContains(50.0, $errorRate['error_rate']);
    }
}
