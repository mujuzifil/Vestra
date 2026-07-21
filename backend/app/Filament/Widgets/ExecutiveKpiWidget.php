<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Cache;

class ExecutiveKpiWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = now()->toDateString();
        $yesterdayStart = now()->subDay()->startOfDay();
        $yesterdayEnd = now()->subDay()->endOfDay();
        $weekStart = now()->startOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();
        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $todayRevenue = Cache::remember(
            "admin.executive.today_revenue.{$today}",
            now()->endOfDay(),
            fn () => Order::paidRevenueBetween(now()->startOfDay(), now()->endOfDay())
        );

        $yesterdayRevenue = Cache::remember(
            "admin.executive.yesterday_revenue.{$today}",
            now()->endOfDay(),
            fn () => Order::paidRevenueBetween($yesterdayStart, $yesterdayEnd)
        );

        $weekRevenue = Cache::remember(
            "admin.executive.week_revenue.{$weekStart->toDateString()}",
            now()->endOfWeek(),
            fn () => Order::paidRevenueBetween($weekStart, now()->endOfWeek())
        );

        $lastWeekRevenue = Cache::remember(
            "admin.executive.last_week_revenue.{$weekStart->toDateString()}",
            now()->endOfWeek(),
            fn () => Order::paidRevenueBetween($lastWeekStart, $lastWeekEnd)
        );

        $monthRevenue = Cache::remember(
            "admin.executive.month_revenue.{$monthStart->toDateString()}",
            now()->endOfMonth(),
            fn () => Order::paidRevenueBetween($monthStart, now()->endOfMonth())
        );

        $lastMonthRevenue = Cache::remember(
            "admin.executive.last_month_revenue.{$monthStart->toDateString()}",
            now()->endOfMonth(),
            fn () => Order::paidRevenueBetween($lastMonthStart, $lastMonthEnd)
        );

        $ordersToday = Cache::remember(
            "admin.executive.orders_today.{$today}",
            now()->endOfDay(),
            fn () => Order::query()->whereDate('created_at', today())->count()
        );

        $ordersYesterday = Cache::remember(
            "admin.executive.orders_yesterday.{$today}",
            now()->endOfDay(),
            fn () => Order::query()->whereDate('created_at', today()->subDay())->count()
        );

        return [
            StatsOverviewWidget\Stat::make('Today\'s Revenue', 'UGX ' . number_format($todayRevenue))
                ->description($this->trendDescription($todayRevenue, $yesterdayRevenue, 'vs yesterday'))
                ->descriptionIcon($this->trendIcon($todayRevenue, $yesterdayRevenue))
                ->color($this->trendColor($todayRevenue, $yesterdayRevenue)),

            StatsOverviewWidget\Stat::make('Weekly Revenue', 'UGX ' . number_format($weekRevenue))
                ->description($this->trendDescription($weekRevenue, $lastWeekRevenue, 'vs last week'))
                ->descriptionIcon($this->trendIcon($weekRevenue, $lastWeekRevenue))
                ->color($this->trendColor($weekRevenue, $lastWeekRevenue)),

            StatsOverviewWidget\Stat::make('Monthly Revenue', 'UGX ' . number_format($monthRevenue))
                ->description($this->trendDescription($monthRevenue, $lastMonthRevenue, 'vs last month'))
                ->descriptionIcon($this->trendIcon($monthRevenue, $lastMonthRevenue))
                ->color($this->trendColor($monthRevenue, $lastMonthRevenue)),

            StatsOverviewWidget\Stat::make('Orders Today', number_format($ordersToday))
                ->description($this->trendDescription($ordersToday, $ordersYesterday, 'vs yesterday'))
                ->descriptionIcon($this->trendIcon($ordersToday, $ordersYesterday))
                ->color($this->trendColor($ordersToday, $ordersYesterday)),
        ];
    }

    private function trendDescription(float $current, float $previous, string $label): string
    {
        if ($previous <= 0) {
            return $current > 0 ? "Up {$label}" : "No change {$label}";
        }

        $change = (($current - $previous) / $previous) * 100;
        $sign = $change >= 0 ? '+' : '';

        return sprintf('%s%.1f%% %s', $sign, $change, $label);
    }

    private function trendIcon(float $current, float $previous): string
    {
        if ($previous <= 0) {
            return $current > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-minus';
        }

        return $current >= $previous ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    private function trendColor(float $current, float $previous): string
    {
        if ($previous <= 0) {
            return $current > 0 ? 'success' : 'gray';
        }

        return $current >= $previous ? 'success' : 'danger';
    }
}
