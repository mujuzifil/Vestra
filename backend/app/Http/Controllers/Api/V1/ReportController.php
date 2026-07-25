<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ApiAnalyticsService;
use App\Services\ForecastingService;
use App\Services\ReportService;
use App\Traits\RespondsWithJson;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly ReportService $service,
        private readonly ForecastingService $forecastingService,
        private readonly ApiAnalyticsService $apiAnalyticsService
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        return $this->successResponse([
            'customers' => $this->service->customerSummary(),
            'inventory' => $this->service->inventorySummary(),
            'engagement' => $this->service->engagementSummary(),
            'distributors' => $this->service->distributorSummary(),
        ]);
    }

    public function executive(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        [$start, $end] = $this->resolveDateRange($request);

        return $this->successResponse($this->service->executiveSummary($start, $end));
    }

    public function revenue(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        [$start, $end] = $this->resolveDateRange($request);
        $granularity = $this->resolveGranularity($request);

        return $this->successResponse([
            'summary' => $this->service->revenueSummary($start, $end),
            'trend' => $this->service->revenueTrend($start, $end, $granularity),
            'by_payment_method' => $this->service->revenueByPaymentMethod($start, $end),
            'by_order_status' => $this->service->revenueByOrderStatus($start, $end),
        ]);
    }

    public function sales(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        [$start, $end] = $this->resolveDateRange($request);
        $granularity = $this->resolveGranularity($request);

        return $this->successResponse([
            'summary' => $this->service->salesSummary($start, $end),
            'trend' => $this->service->ordersTrend($start, $end, $granularity),
            'best_sellers' => $this->service->bestSellers($start, $end, $this->limit($request, 10)),
            'worst_performers' => $this->service->worstPerformers($start, $end, $this->limit($request, 10)),
            'by_category' => $this->service->salesByCategory($start, $end, $this->limit($request, 10)),
        ]);
    }

    public function salesTrend(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        $period = $request->validate(['period' => 'in:daily,weekly,monthly'])['period'] ?? 'daily';
        $limit = min((int) ($request->get('limit', 30)), 365);

        $granularity = match ($period) {
            'weekly' => 'week',
            'monthly' => 'month',
            default => 'day',
        };

        $end = Carbon::now()->endOfDay();
        $start = match ($granularity) {
            'week' => $end->copy()->subWeeks($limit)->startOfWeek(),
            'month' => $end->copy()->subMonths($limit)->startOfMonth(),
            default => $end->copy()->subDays($limit - 1)->startOfDay(),
        };

        return $this->successResponse($this->service->ordersTrend($start, $end, $granularity));
    }

    public function bestSellers(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        $limit = min((int) ($request->get('limit', 10)), 100);
        $end = Carbon::now()->endOfDay();
        $start = $end->copy()->subDays(30)->startOfDay();

        return $this->successResponse($this->service->bestSellers($start, $end, $limit));
    }

    public function inventory(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        return $this->successResponse([
            'summary' => $this->service->inventorySummary(),
            'low_stock' => $this->service->lowStock(10, $this->limit($request, 10)),
            'out_of_stock' => $this->service->outOfStock($this->limit($request, 10)),
        ]);
    }

    public function inventoryValue(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        return $this->successResponse($this->service->inventorySummary());
    }

    public function customerGrowth(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        $months = min((int) ($request->get('months', 6)), 24);
        $end = Carbon::now()->endOfMonth();
        $start = $end->copy()->subMonths($months - 1)->startOfMonth();

        return $this->successResponse($this->service->customerGrowth($start, $end, 'month'));
    }

    public function customers(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        [$start, $end] = $this->resolveDateRange($request);
        $months = min((int) ($request->get('months', 6)), 24);
        $growthEnd = Carbon::now()->endOfMonth();
        $growthStart = $growthEnd->copy()->subMonths($months - 1)->startOfMonth();

        return $this->successResponse([
            'summary' => $this->service->customerSummary(),
            'growth' => $this->service->customerGrowth($growthStart, $growthEnd, 'month'),
            'top_customers' => $this->service->topCustomers($start, $end, $this->limit($request, 10)),
            'inactive' => $this->service->inactiveCustomers(90, $this->limit($request, 10)),
            'average_value' => $this->service->averageCustomerValue(),
        ]);
    }

    public function customerIntelligence(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        [$start, $end] = $this->resolveDateRange($request);

        return $this->successResponse([
            'segments' => $this->service->customerSegments(),
            'lifetime_value' => $this->service->customerLifetimeValue($this->limit($request, 20)),
            'retention_rate' => $this->service->customerRetentionRate($start, $end),
            'churn_rate' => $this->service->customerChurnRate(90),
            'top_regions' => $this->service->topCustomerRegions($this->limit($request, 10)),
            'activity_timeline' => $this->service->customerActivityTimeline($this->limit($request, 50)),
        ]);
    }

    public function distributors(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        [$start, $end] = $this->resolveDateRange($request);

        return $this->successResponse([
            'summary' => $this->service->distributorSummary(),
            'revenue' => $this->service->distributorRevenue($start, $end),
            'orders' => $this->service->distributorOrders($start, $end),
        ]);
    }

    public function distributorIntelligence(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        [$start, $end] = $this->resolveDateRange($request);

        return $this->successResponse([
            'credit_utilization' => $this->service->distributorCreditUtilization(),
            'outstanding_balances' => $this->service->distributorOutstandingBalances(),
            'performance_trend' => $this->service->distributorPerformanceTrend($start, $end),
            'top_distributors' => $this->service->distributorRevenue($start, $end),
        ]);
    }

    public function inventoryIntelligence(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        [$start, $end] = $this->resolveDateRange($request);

        return $this->successResponse([
            'turnover' => $this->service->inventoryTurnover($start, $end),
            'valuation_by_category' => $this->service->stockValuationByCategory(),
            'warehouse_utilization' => $this->service->warehouseUtilization(),
            'dead_stock' => $this->service->deadStock(90, $this->limit($request, 20)),
            'fast_moving' => $this->service->fastMovingProducts($start, $end, $this->limit($request, 10)),
            'slow_moving' => $this->service->slowMovingProducts($start, $end, $this->limit($request, 10)),
        ]);
    }

    public function engagement(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        [$start, $end] = $this->resolveDateRange($request);

        return $this->successResponse([
            'summary' => $this->service->engagementSummary(),
            'review_statistics' => $this->service->reviewStatistics(),
            'review_analytics' => $this->service->reviewAnalytics(30),
            'trend' => $this->service->engagementTrend($start, $end),
            'wishlist' => $this->service->wishlistAnalytics(),
            'recommendations' => $this->service->recommendationEffectiveness(30),
        ]);
    }

    public function searchAnalytics(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        return $this->successResponse($this->service->searchConversionMetrics(30));
    }

    public function forecast(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        $days = min((int) ($request->get('days', 30)), 90);
        $months = min((int) ($request->get('months', 3)), 12);

        return $this->successResponse([
            'revenue' => $this->forecastingService->revenueForecast($days),
            'orders' => $this->forecastingService->orderForecast($days),
            'inventory' => $this->forecastingService->inventoryForecast($days),
            'customer_growth' => $this->forecastingService->customerGrowthForecast($months),
            'distributor_growth' => $this->forecastingService->distributorGrowthForecast($months),
        ]);
    }

    public function apiAnalytics(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        $days = min((int) ($request->get('days', 30)), 90);

        return $this->successResponse($this->apiAnalyticsService->summary($days));
    }

    public function operational(Request $request): JsonResponse
    {
        $this->authorize('view reports');

        return $this->successResponse([
            'queue_health' => $this->service->queueHealth(),
            'recent_failed_jobs' => $this->service->recentFailedJobs($this->limit($request, 10)),
            'scheduler_status' => $this->service->schedulerStatus(),
            'storage_status' => $this->service->storageStatus(),
            'cache_status' => $this->service->cacheStatus(),
            'notification_delivery' => $this->service->notificationDeliveryMetrics(30),
        ]);
    }

    private function resolveDateRange(Request $request): array
    {
        $validated = $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
        ]);

        $end = isset($validated['end']) ? Carbon::parse($validated['end'])->endOfDay() : Carbon::now()->endOfDay();
        $start = isset($validated['start']) ? Carbon::parse($validated['start'])->startOfDay() : $end->copy()->subDays(29)->startOfDay();

        return [$start, $end];
    }

    private function resolveGranularity(Request $request): string
    {
        $period = $request->get('period', 'daily');

        return match ($period) {
            'weekly' => 'week',
            'monthly' => 'month',
            default => 'day',
        };
    }

    private function limit(Request $request, int $default): int
    {
        return min((int) ($request->get('limit', $default)), 100);
    }
}
