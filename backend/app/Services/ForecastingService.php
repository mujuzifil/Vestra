<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\DistributorRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ForecastingService
{
    private const CACHE_TTL = 3600;

    /**
     * Forecast daily revenue for the next N days using a weighted moving average
     * of the last N days, blended with a simple linear regression trend.
     */
    public function revenueForecast(int $days = 30): array
    {
        $key = "forecast.revenue.{$days}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days): array {
            $historyDays = max($days, 60);
            $end = now()->subDay()->endOfDay();
            $start = now()->subDays($historyDays)->startOfDay();

            $daily = Order::query()
                ->paid()
                ->whereBetween('created_at', [$start, $end])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as orders'))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('revenue', 'date')
                ->toArray();

            $labels = [];
            $historical = [];
            $forecast = [];

            $current = $start->copy();
            while ($current <= $end) {
                $dateKey = $current->toDateString();
                $labels[] = $current->format('M d');
                $historical[] = (float) ($daily[$dateKey] ?? 0);
                $forecast[] = null;
                $current->addDay();
            }

            $trend = $this->linearRegression(array_values($historical));
            $baseAverage = $this->weightedMovingAverage(array_values($historical), min(14, count($historical)));

            $forecastStartIndex = count($historical);
            $current = $end->copy()->addDay();
            for ($i = 0; $i < $days; $i++) {
                $labels[] = $current->format('M d');
                $historical[] = null;
                $trendComponent = $trend['slope'] * ($forecastStartIndex + $i) + $trend['intercept'];
                $forecastValue = ($baseAverage * 0.6) + ($trendComponent * 0.4);
                $forecast[] = max(0, round($forecastValue, 2));
                $current->addDay();
            }

            return [
                'labels' => $labels,
                'historical' => $historical,
                'forecast' => $forecast,
                'trend' => $trend,
            ];
        });
    }

    public function orderForecast(int $days = 30): array
    {
        $key = "forecast.orders.{$days}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days): array {
            $historyDays = max($days, 60);
            $end = now()->subDay()->endOfDay();
            $start = now()->subDays($historyDays)->startOfDay();

            $daily = Order::query()
                ->whereBetween('created_at', [$start, $end])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as orders'))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('orders', 'date')
                ->toArray();

            $values = [];
            $labels = [];
            $current = $start->copy();
            while ($current <= $end) {
                $values[] = (int) ($daily[$current->toDateString()] ?? 0);
                $labels[] = $current->format('M d');
                $current->addDay();
            }

            $trend = $this->linearRegression($values);
            $baseAverage = $this->weightedMovingAverage($values, min(14, count($values)));

            $forecastStartIndex = count($values);
            $current = $end->copy()->addDay();
            $forecastValues = [];
            for ($i = 0; $i < $days; $i++) {
                $labels[] = $current->format('M d');
                $forecastValues[] = max(0, (int) round(($baseAverage * 0.6) + (($trend['slope'] * ($forecastStartIndex + $i) + $trend['intercept']) * 0.4)));
                $current->addDay();
            }

            return [
                'labels' => $labels,
                'historical' => $values,
                'forecast' => $forecastValues,
            ];
        });
    }

    public function inventoryForecast(int $days = 30): array
    {
        $key = "forecast.inventory.{$days}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days): array {
            $end = now()->endOfDay();
            $start = now()->subDays(30)->startOfDay();

            $products = Product::query()
                ->where('stock_quantity', '>', 0)
                ->withSum(['orderItems as sold_30d' => fn ($q) => $q->whereHas('order', fn ($oq) => $oq->paid()->whereBetween('created_at', [$start, $end]))], 'quantity')
                ->orderByDesc('sold_30d')
                ->limit(50)
                ->get();

            $atRisk = [];
            foreach ($products as $product) {
                $sold = (int) ($product->sold_30d ?? 0);
                $dailyVelocity = $sold / 30;
                $daysUntilStockout = $dailyVelocity > 0 ? $product->stock_quantity / $dailyVelocity : null;

                if ($daysUntilStockout !== null && $daysUntilStockout <= $days) {
                    $atRisk[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'stock_quantity' => $product->stock_quantity,
                        'sold_30d' => $sold,
                        'daily_velocity' => round($dailyVelocity, 2),
                        'projected_stockout_days' => (int) ceil($daysUntilStockout),
                    ];
                }
            }

            usort($atRisk, fn ($a, $b) => $a['projected_stockout_days'] <=> $b['projected_stockout_days']);

            return [
                'forecast_days' => $days,
                'at_risk_products' => array_slice($atRisk, 0, 20),
                'total_at_risk' => count($atRisk),
            ];
        });
    }

    public function customerGrowthForecast(int $months = 3): array
    {
        $key = "forecast.customers.{$months}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($months): array {
            $end = now()->subMonth()->endOfMonth();
            $start = now()->subMonths(6)->startOfMonth();

            $monthly = User::query()
                ->where('is_admin', false)
                ->whereBetween('created_at', [$start, $end])
                ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COUNT(*) as count'))
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            $values = array_values($monthly);
            $labels = array_keys($monthly);
            $trend = $this->linearRegression($values);

            $forecastLabels = [];
            $forecastValues = [];
            $current = $end->copy()->addMonth();
            for ($i = 0; $i < $months; $i++) {
                $forecastLabels[] = $current->format('M Y');
                $forecastValues[] = max(0, (int) round($trend['slope'] * (count($values) + $i) + $trend['intercept']));
                $current->addMonth();
            }

            return [
                'labels' => array_merge($labels, $forecastLabels),
                'historical' => $values,
                'forecast' => array_merge(array_fill(0, count($values), null), $forecastValues),
            ];
        });
    }

    public function distributorGrowthForecast(int $months = 3): array
    {
        $key = "forecast.distributors.{$months}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($months): array {
            $end = now()->subMonth()->endOfMonth();
            $start = now()->subMonths(6)->startOfMonth();

            $monthly = DistributorRequest::query()
                ->whereBetween('created_at', [$start, $end])
                ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COUNT(*) as count'))
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            $values = array_values($monthly);
            $labels = array_keys($monthly);
            $trend = $this->linearRegression($values);

            $forecastLabels = [];
            $forecastValues = [];
            $current = $end->copy()->addMonth();
            for ($i = 0; $i < $months; $i++) {
                $forecastLabels[] = $current->format('M Y');
                $forecastValues[] = max(0, (int) round($trend['slope'] * (count($values) + $i) + $trend['intercept']));
                $current->addMonth();
            }

            return [
                'labels' => array_merge($labels, $forecastLabels),
                'historical' => $values,
                'forecast' => array_merge(array_fill(0, count($values), null), $forecastValues),
            ];
        });
    }

    /**
     * @param  array<int, float|int>  $data
     * @return array{slope: float, intercept: float}
     */
    private function linearRegression(array $data): array
    {
        $n = count($data);
        if ($n === 0) {
            return ['slope' => 0.0, 'intercept' => 0.0];
        }

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($data as $index => $value) {
            $x = $index;
            $y = (float) $value;
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $denominator = ($n * $sumX2) - ($sumX * $sumX);
        if ($denominator === 0.0) {
            return ['slope' => 0.0, 'intercept' => $sumY / $n];
        }

        $slope = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
        $intercept = ($sumY - ($slope * $sumX)) / $n;

        return [
            'slope' => (float) $slope,
            'intercept' => (float) $intercept,
        ];
    }

    /**
     * @param  array<int, float|int>  $data
     */
    private function weightedMovingAverage(array $data, int $window): float
    {
        $count = count($data);
        if ($count === 0) {
            return 0.0;
        }

        $window = min($window, $count);
        $slice = array_slice($data, -$window);
        $weightedSum = 0;
        $weightSum = 0;

        foreach ($slice as $index => $value) {
            $weight = $index + 1;
            $weightedSum += (float) $value * $weight;
            $weightSum += $weight;
        }

        return $weightSum > 0 ? $weightedSum / $weightSum : 0.0;
    }
}
