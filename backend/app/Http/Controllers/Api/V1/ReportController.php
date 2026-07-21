<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ReportService $service) {}

    public function dashboard(Request $request): JsonResponse
    {
        return $this->successResponse($this->service->dashboardSummary());
    }

    public function salesTrend(Request $request): JsonResponse
    {
        $period = $request->validate(['period' => 'in:daily,weekly,monthly'])['period'] ?? 'daily';
        $limit = min((int) ($request->get('limit', 30)), 365);

        return $this->successResponse($this->service->salesTrend($period, $limit));
    }

    public function bestSellers(Request $request): JsonResponse
    {
        $limit = min((int) ($request->get('limit', 10)), 100);

        return $this->successResponse($this->service->bestSellers($limit));
    }

    public function inventoryValue(Request $request): JsonResponse
    {
        return $this->successResponse($this->service->inventoryValue());
    }

    public function customerGrowth(Request $request): JsonResponse
    {
        $months = min((int) ($request->get('months', 6)), 24);

        return $this->successResponse($this->service->customerGrowth($months));
    }
}
