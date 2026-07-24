<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Distributor\AnalyticsResource;
use App\Traits\RespondsWithJson;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;
        $period = $request->input('period', '30_days');

        $startDate = match ($period) {
            '7_days' => Carbon::now()->subDays(7),
            '90_days' => Carbon::now()->subDays(90),
            'this_year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->subDays(30),
        };

        $ordersQuery = $distributor->orders()
            ->where('created_at', '>=', $startDate);

        $ordersCount = $ordersQuery->count();
        $ordersTotal = (float) $ordersQuery->sum('total_amount');
        $averageOrderValue = $ordersCount > 0 ? $ordersTotal / $ordersCount : 0.0;

        $totalOrders = $distributor->orders()->count();
        $totalQuotes = $distributor->quotations()->count();
        $pendingQuotes = $distributor->quotations()
            ->whereIn('status', [QuotationStatus::DRAFT->value, QuotationStatus::SUBMITTED->value, QuotationStatus::REVIEWED->value, QuotationStatus::QUOTED->value])
            ->count();

        $quotationsCount = $distributor->quotations()
            ->where('created_at', '>=', $startDate)
            ->count();

        $acceptedQuotationsCount = $distributor->quotations()
            ->where('created_at', '>=', $startDate)
            ->where('status', QuotationStatus::ACCEPTED->value)
            ->count();

        $pendingPaymentsTotal = (float) $distributor->orders()
            ->where('payment_status', PaymentStatus::PENDING->value)
            ->sum('total_amount');

        $creditAccount = $distributor->creditAccount;
        $creditUtilization = $creditAccount ? $creditAccount->utilizationPercentage() : 0.0;

        $topProducts = $distributor->orders()
            ->with('items.product')
            ->where('created_at', '>=', $startDate)
            ->get()
            ->pluck('items')
            ->flatten()
            ->groupBy('product_id')
            ->map(fn ($items) => [
                'product_id' => $items->first()->product_id,
                'product_name' => $items->first()->product_name,
                'total_quantity' => $items->sum('quantity'),
                'total_revenue' => number_format((float) $items->sum('line_total'), 2),
            ])
            ->sortByDesc('total_quantity')
            ->take(5)
            ->values();

        $ordersByStatus = $this->ordersByStatus($distributor, $startDate);
        $revenueByMonth = $this->revenueByMonth($distributor);

        $previousPeriodTotal = (float) $distributor->orders()
            ->whereBetween('created_at', [$startDate->copy()->subDays($startDate->diffInDays(now())), $startDate])
            ->sum('total_amount');

        $monthOverMonthGrowth = $previousPeriodTotal > 0
            ? round((($ordersTotal - $previousPeriodTotal) / $previousPeriodTotal) * 100, 2)
            : 0.0;

        return $this->successResponse(
            new AnalyticsResource([
                'period' => $period,
                'total_orders' => $totalOrders,
                'total_revenue' => number_format($ordersTotal, 2),
                'total_quotes' => $totalQuotes,
                'pending_quotes' => $pendingQuotes,
                'average_order_value' => number_format($averageOrderValue, 2),
                'month_over_month_growth' => $monthOverMonthGrowth,
                'orders_by_status' => $ordersByStatus,
                'revenue_by_month' => $revenueByMonth,
                'orders_count' => $ordersCount,
                'orders_total' => number_format($ordersTotal, 2),
                'quotations_count' => $quotationsCount,
                'accepted_quotations_count' => $acceptedQuotationsCount,
                'pending_payments_total' => number_format($pendingPaymentsTotal, 2),
                'credit_utilization_percentage' => number_format($creditUtilization, 2),
                'top_products' => $topProducts,
            ])
        );
    }

    private function ordersByStatus($distributor, Carbon $startDate): array
    {
        $counts = $distributor->orders()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $result = [];
        foreach (OrderStatus::cases() as $case) {
            $result[$case->value] = (int) ($counts[$case->value] ?? 0);
        }

        return $result;
    }

    private function revenueByMonth($distributor): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => Carbon::now()->subMonths($i))->values();

        return $months->map(fn (Carbon $month) => [
            'month' => $month->format('Y-m'),
            'revenue' => number_format((float) $distributor->orders()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_amount'), 2),
        ])->values()->all();
    }
}
