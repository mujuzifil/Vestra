<?php

namespace App\Services;

use App\Models\ApiRequestLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApiAnalyticsService
{
    private const CACHE_TTL = 600;

    public function requestVolume(int $days = 30): array
    {
        $key = "api_analytics.volume.{$days}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days): array {
            $end = now()->endOfDay();
            $start = now()->subDays($days - 1)->startOfDay();

            $rows = ApiRequestLog::query()
                ->whereBetween('created_at', [$start, $end])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray();

            $labels = [];
            $values = [];
            $current = $start->copy();
            while ($current <= $end) {
                $labels[] = $current->format('M d');
                $values[] = (int) ($rows[$current->toDateString()] ?? 0);
                $current->addDay();
            }

            return ['labels' => $labels, 'requests' => $values];
        });
    }

    public function topEndpoints(int $days = 30, int $limit = 20): array
    {
        $key = "api_analytics.endpoints.{$days}.{$limit}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days, $limit): array {
            return ApiRequestLog::query()
                ->where('created_at', '>=', now()->subDays($days))
                ->select('method', 'path', DB::raw('COUNT(*) as count'), DB::raw('AVG(duration_ms) as avg_duration'), DB::raw('SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as errors'))
                ->groupBy('method', 'path')
                ->orderByDesc('count')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => [
                    'method' => $row->method,
                    'path' => $row->path,
                    'count' => (int) $row->count,
                    'avg_duration_ms' => (float) ($row->avg_duration ?? 0),
                    'errors' => (int) $row->errors,
                ])
                ->toArray();
        });
    }

    public function errorRate(int $days = 30): array
    {
        $key = "api_analytics.errors.{$days}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days): array {
            $end = now()->endOfDay();
            $start = now()->subDays($days - 1)->startOfDay();

            $rows = ApiRequestLog::query()
                ->whereBetween('created_at', [$start, $end])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as errors'))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $labels = [];
            $errorRates = [];
            $current = $start->copy();
            while ($current <= $end) {
                $dateKey = $current->toDateString();
                $row = $rows->get($dateKey);
                $labels[] = $current->format('M d');
                $total = (int) ($row?->total ?? 0);
                $errors = (int) ($row?->errors ?? 0);
                $errorRates[] = $total > 0 ? round(($errors / $total) * 100, 2) : 0;
                $current->addDay();
            }

            return ['labels' => $labels, 'error_rate' => $errorRates];
        });
    }

    public function averageLatency(int $days = 30): array
    {
        $key = "api_analytics.latency.{$days}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days): array {
            $end = now()->endOfDay();
            $start = now()->subDays($days - 1)->startOfDay();

            $rows = ApiRequestLog::query()
                ->whereBetween('created_at', [$start, $end])
                ->whereNotNull('duration_ms')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('AVG(duration_ms) as avg_duration'), DB::raw('MAX(duration_ms) as max_duration'))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('avg_duration', 'date')
                ->toArray();

            $labels = [];
            $values = [];
            $current = $start->copy();
            while ($current <= $end) {
                $labels[] = $current->format('M d');
                $values[] = (float) ($rows[$current->toDateString()] ?? 0);
                $current->addDay();
            }

            return ['labels' => $labels, 'avg_latency_ms' => $values];
        });
    }

    public function authFailureRate(int $days = 30): array
    {
        $key = "api_analytics.auth_failures.{$days}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days): array {
            $end = now()->endOfDay();
            $start = now()->subDays($days - 1)->startOfDay();

            $total = ApiRequestLog::query()->whereBetween('created_at', [$start, $end])->where('path', 'like', '%/auth/%')->count();
            $failures = ApiRequestLog::query()
                ->whereBetween('created_at', [$start, $end])
                ->where('path', 'like', '%/auth/%')
                ->whereIn('status_code', [401, 403, 422, 429])
                ->count();

            return [
                'total_auth_requests' => (int) $total,
                'failed_auth_requests' => (int) $failures,
                'failure_rate' => $total > 0 ? round(($failures / $total) * 100, 2) : 0,
            ];
        });
    }

    public function summary(int $days = 30): array
    {
        return [
            'volume' => $this->requestVolume($days),
            'top_endpoints' => $this->topEndpoints($days),
            'error_rate' => $this->errorRate($days),
            'average_latency' => $this->averageLatency($days),
            'auth_failure_rate' => $this->authFailureRate($days),
        ];
    }
}
