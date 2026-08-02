<?php

namespace App\Filament\Widgets;

use App\Enums\DistributorStatus;
use App\Enums\ProductStatus;
use App\Enums\QuoteRequestStatus;
use App\Models\DistributorRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\SupportTicket;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Cache;

class KpiCardsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        return [
            $this->openQuotesStat(),
            $this->pendingDistributorApplicationsStat(),
            $this->openSupportTicketsStat(),
            $this->revenueStat(),
            $this->productsStat(),
        ];
    }

    private function openQuotesStat(): StatsOverviewWidget\Stat
    {
        $current = Cache::remember('admin.kpi.open_quotes', 300, fn () => QuoteRequest::query()
            ->whereIn('status', [
                QuoteRequestStatus::PENDING->value,
                QuoteRequestStatus::CONTACTED->value,
                QuoteRequestStatus::QUOTED->value,
            ])
            ->count());

        $previous = Cache::remember('admin.kpi.open_quotes_previous', 300, fn () => QuoteRequest::query()
            ->whereIn('status', [
                QuoteRequestStatus::PENDING->value,
                QuoteRequestStatus::CONTACTED->value,
                QuoteRequestStatus::QUOTED->value,
            ])
            ->where('created_at', '<', now()->subDays(7))
            ->count());

        return StatsOverviewWidget\Stat::make('Open Quotes', number_format($current))
            ->description($this->trendDescription($current, $previous, 'vs last 7 days'))
            ->descriptionIcon($this->trendIcon($current, $previous))
            ->color($this->trendColor($current, $previous))
            ->icon('heroicon-o-document-text');
    }

    private function pendingDistributorApplicationsStat(): StatsOverviewWidget\Stat
    {
        $current = Cache::remember('admin.kpi.pending_distributor_applications', 300, fn () => DistributorRequest::awaitingReview()->count());

        $previous = Cache::remember('admin.kpi.pending_distributor_applications_previous', 300, fn () => DistributorRequest::awaitingReview()
            ->where('created_at', '<', now()->subDays(7))
            ->count());

        return StatsOverviewWidget\Stat::make('Pending Applications', number_format($current))
            ->description($this->trendDescription($current, $previous, 'vs last 7 days'))
            ->descriptionIcon($this->trendIcon($current, $previous))
            ->color($this->trendColor($current, $previous))
            ->icon('heroicon-o-user-group');
    }

    private function openSupportTicketsStat(): StatsOverviewWidget\Stat
    {
        $current = Cache::remember('admin.kpi.open_support_tickets', 300, fn () => SupportTicket::query()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count());

        $previous = Cache::remember('admin.kpi.open_support_tickets_previous', 300, fn () => SupportTicket::query()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->where('created_at', '<', now()->subDays(7))
            ->count());

        return StatsOverviewWidget\Stat::make('Open Tickets', number_format($current))
            ->description($this->trendDescription($current, $previous, 'vs last 7 days'))
            ->descriptionIcon($this->trendIcon($current, $previous))
            ->color($this->trendColor($current, $previous))
            ->icon('heroicon-o-ticket');
    }

    private function revenueStat(): StatsOverviewWidget\Stat
    {
        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $current = Cache::remember('admin.kpi.revenue_mtd', 300, fn () => Order::paidRevenueBetween($monthStart, now()->endOfDay()));
        $previous = Cache::remember('admin.kpi.revenue_mtd_previous', 300, fn () => Order::paidRevenueBetween($lastMonthStart, $lastMonthEnd));

        return StatsOverviewWidget\Stat::make('Revenue (MTD)', 'UGX ' . $this->formatMoney($current))
            ->description($this->trendDescription($current, $previous, 'vs last month'))
            ->descriptionIcon($this->trendIcon($current, $previous))
            ->color($this->trendColor($current, $previous))
            ->icon('heroicon-o-banknotes');
    }

    private function productsStat(): StatsOverviewWidget\Stat
    {
        $current = Cache::remember('admin.kpi.products', 300, fn () => Product::query()
            ->where('status', '!=', ProductStatus::INACTIVE->value)
            ->count());

        $previous = Cache::remember('admin.kpi.products_previous', 300, fn () => Product::query()
            ->where('status', '!=', ProductStatus::INACTIVE->value)
            ->where('created_at', '<', now()->subDays(30))
            ->count());

        return StatsOverviewWidget\Stat::make('Products', number_format($current))
            ->description($this->trendDescription($current, $previous, 'vs last 30 days'))
            ->descriptionIcon($this->trendIcon($current, $previous))
            ->color($this->trendColor($current, $previous))
            ->icon('heroicon-o-cube');
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

    private function formatMoney(float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return number_format($amount / 1_000_000_000, 2) . 'B';
        }

        if ($amount >= 1_000_000) {
            return number_format($amount / 1_000_000, 2) . 'M';
        }

        if ($amount >= 1_000) {
            return number_format($amount / 1_000, 2) . 'k';
        }

        return number_format($amount, 2);
    }
}
