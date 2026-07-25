<?php

namespace App\Filament\Widgets;

use App\Services\ApiAnalyticsService;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Cache;

class ApiHealthWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $data = Cache::remember('admin.widgets.api_health', 300, function (): array {
            $service = app(ApiAnalyticsService::class);
            $volume = $service->requestVolume(1);
            $errors = $service->errorRate(1);
            $latency = $service->averageLatency(1);
            $auth = $service->authFailureRate(1);

            return [
                'requests_today' => array_sum($volume['requests']),
                'error_rate' => ! empty($errors['error_rate']) ? array_sum($errors['error_rate']) / count($errors['error_rate']) : 0,
                'avg_latency' => ! empty($latency['avg_latency_ms']) ? array_sum($latency['avg_latency_ms']) / count($latency['avg_latency_ms']) : 0,
                'auth_failure_rate' => $auth['failure_rate'],
            ];
        });

        return [
            StatsOverviewWidget\Stat::make('API Requests Today', number_format($data['requests_today']))
                ->description('Authenticated & admin routes')
                ->icon('heroicon-m-signal')
                ->color('primary'),

            StatsOverviewWidget\Stat::make('Avg Latency', number_format($data['avg_latency'], 0) . ' ms')
                ->description('Last 24 hours')
                ->icon('heroicon-m-clock')
                ->color($data['avg_latency'] > 500 ? 'warning' : 'success'),

            StatsOverviewWidget\Stat::make('Error Rate', number_format($data['error_rate'], 2) . '%')
                ->description('Last 24 hours')
                ->icon('heroicon-m-exclamation-triangle')
                ->color($data['error_rate'] > 5 ? 'danger' : 'success'),

            StatsOverviewWidget\Stat::make('Auth Failure Rate', number_format($data['auth_failure_rate'], 2) . '%')
                ->description('Last 24 hours')
                ->icon('heroicon-m-shield-exclamation')
                ->color($data['auth_failure_rate'] > 5 ? 'danger' : 'success'),
        ];
    }
}
