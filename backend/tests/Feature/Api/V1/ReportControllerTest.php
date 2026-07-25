<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $admin = User::where('email', 'admin@vestra.com')->first();
        if ($admin) {
            $admin->forceFill([
                'password' => 'Admin@12345',
                'force_password_change_at' => null,
                'status' => 'active',
            ])->saveQuietly();
            $admin->syncRoles(['Super Administrator']);
        }
    }

    private function actingAsAdmin(): self
    {
        $admin = User::where('email', 'admin@vestra.com')->firstOrFail();

        return $this->actingAs($admin, 'sanctum');
    }

    public function test_dashboard_report_requires_authentication(): void
    {
        $this->getJson('/api/v1/reports/dashboard')->assertUnauthorized();
    }

    public function test_dashboard_report_returns_summary_data(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/v1/reports/dashboard');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'customers',
                    'inventory',
                    'engagement',
                    'distributors',
                ],
            ]);
    }

    public function test_executive_report_returns_comprehensive_kpis(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/v1/reports/executive');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'revenue',
                    'sales',
                    'customers',
                    'inventory',
                    'engagement',
                    'distributors',
                    'finance',
                    'period',
                ],
            ]);
    }

    public function test_customer_intelligence_report_returns_segments_and_clv(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/v1/reports/customer-intelligence');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'segments',
                    'lifetime_value',
                    'retention_rate',
                    'churn_rate',
                    'top_regions',
                    'activity_timeline',
                ],
            ]);
    }

    public function test_distributor_intelligence_report_returns_credit_and_balances(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/v1/reports/distributor-intelligence');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'credit_utilization',
                    'outstanding_balances',
                    'performance_trend',
                    'top_distributors',
                ],
            ]);
    }

    public function test_inventory_intelligence_report_returns_turnover_and_valuation(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/v1/reports/inventory-intelligence');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'turnover',
                    'valuation_by_category',
                    'warehouse_utilization',
                    'dead_stock',
                    'fast_moving',
                    'slow_moving',
                ],
            ]);
    }

    public function test_forecast_report_returns_forecasts(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/v1/reports/forecast');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'revenue',
                    'orders',
                    'inventory',
                    'customer_growth',
                    'distributor_growth',
                ],
            ]);
    }

    public function test_api_analytics_report_returns_metrics(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/v1/reports/api-analytics');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'volume',
                    'top_endpoints',
                    'error_rate',
                    'average_latency',
                    'auth_failure_rate',
                ],
            ]);
    }

    public function test_operational_report_returns_monitoring_data(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/v1/reports/operational');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'queue_health',
                    'recent_failed_jobs',
                    'scheduler_status',
                    'storage_status',
                    'cache_status',
                    'notification_delivery',
                ],
            ]);
    }

    public function test_reports_authorize_view_reports_permission(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'status' => 'active']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/reports/dashboard')
            ->assertForbidden();
    }
}
