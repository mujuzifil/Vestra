<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Traits\RespondsWithJson;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ReportService $service) {}

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
}
