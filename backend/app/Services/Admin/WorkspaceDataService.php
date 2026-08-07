<?php

namespace App\Services\Admin;

use App\Enums\DistributorStatus;
use App\Enums\ProductStatus;
use App\Enums\QuoteRequestStatus;
use App\Models\DistributorRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class WorkspaceDataService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        return [
            $this->openQuotesCard(),
            $this->pendingApplicationsCard(),
            $this->openTicketsCard(),
            $this->revenueCard(),
            $this->productsCard(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getSalesOverviewData(string $period): array
    {
        [$start, $end] = $this->resolvePeriodBounds($period);

        $cacheKey = 'admin.workspace.sales_overview.' . $period . '.' . $start->toDateString() . '.' . $end->toDateString();

        return Cache::remember($cacheKey, 3600, function () use ($start, $end): array {
            $labels = [];
            $values = [];

            $current = $start->copy();
            while ($current <= $end) {
                $labels[] = $current->format('M d');
                $values[] = (float) QuoteRequest::query()
                    ->whereDate('created_at', $current)
                    ->sum('estimated_value');
                $current->addDay();
            }

            return compact('labels', 'values');
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentActivities(): array
    {
        return app(ActivityService::class)->getRecentForDashboard(6);
    }

    private function openQuotesCard(): array
    {
        $current = $this->cached('admin.kpi.open_quotes', fn () => QuoteRequest::query()
            ->whereIn('status', [
                QuoteRequestStatus::PENDING->value,
                QuoteRequestStatus::CONTACTED->value,
                QuoteRequestStatus::QUOTED->value,
            ])
            ->count());

        $previous = $this->cached('admin.kpi.open_quotes_previous', fn () => QuoteRequest::query()
            ->whereIn('status', [
                QuoteRequestStatus::PENDING->value,
                QuoteRequestStatus::CONTACTED->value,
                QuoteRequestStatus::QUOTED->value,
            ])
            ->where('created_at', '<', now()->subDays(7))
            ->count());

        return $this->buildCard('Open Quotes', $current, $previous, 'vs last 7 days', 'heroicon-o-document-text', 'primary');
    }

    private function pendingApplicationsCard(): array
    {
        $current = $this->cached('admin.kpi.pending_distributor_applications', fn () => DistributorRequest::awaitingReview()->count());
        $previous = $this->cached('admin.kpi.pending_distributor_applications_previous', fn () => DistributorRequest::awaitingReview()
            ->where('created_at', '<', now()->subDays(7))
            ->count());

        return $this->buildCard('Pending Applications', $current, $previous, 'vs last 7 days', 'heroicon-o-user-group', 'warning');
    }

    private function openTicketsCard(): array
    {
        $current = $this->cached('admin.kpi.open_support_tickets', fn () => SupportTicket::query()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count());

        $previous = $this->cached('admin.kpi.open_support_tickets_previous', fn () => SupportTicket::query()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->where('created_at', '<', now()->subDays(7))
            ->count());

        return $this->buildCard('Open Tickets', $current, $previous, 'vs last 7 days', 'heroicon-o-ticket', 'danger');
    }

    private function revenueCard(): array
    {
        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $current = $this->cached('admin.kpi.revenue_mtd', fn () => Order::paidRevenueBetween($monthStart, now()->endOfDay()));
        $previous = $this->cached('admin.kpi.revenue_mtd_previous', fn () => Order::paidRevenueBetween($lastMonthStart, $lastMonthEnd));

        return $this->buildCard('Revenue (MTD)', $current, $previous, 'vs last month', 'heroicon-o-banknotes', 'success', prefix: 'UGX ');
    }

    private function productsCard(): array
    {
        $current = $this->cached('admin.kpi.products', fn () => Product::query()
            ->where('status', '!=', ProductStatus::INACTIVE->value)
            ->count());

        $previous = $this->cached('admin.kpi.products_previous', fn () => Product::query()
            ->where('status', '!=', ProductStatus::INACTIVE->value)
            ->where('created_at', '<', now()->subDays(30))
            ->count());

        return $this->buildCard('Products', $current, $previous, 'vs last 30 days', 'heroicon-o-cube', 'info');
    }

    private function buildCard(string $label, float $current, float $previous, string $comparisonLabel, string $icon, string $color, string $prefix = ''): array
    {
        $trend = $this->calculateTrend($current, $previous);

        return [
            'label' => $label,
            'value' => $prefix . ($label === 'Revenue (MTD)' ? $this->formatMoney($current) : number_format($current)),
            'icon' => $icon,
            'color' => $color,
            'trend' => $trend['value'],
            'trend_label' => $trend['label'] . ' ' . $comparisonLabel,
            'trend_positive' => $trend['positive'],
        ];
    }

    private function calculateTrend(float $current, float $previous): array
    {
        if ($previous <= 0) {
            return [
                'value' => $current > 0 ? '+100%' : '0%',
                'label' => $current > 0 ? 'Up' : 'No change',
                'positive' => $current > 0,
            ];
        }

        $change = (($current - $previous) / $previous) * 100;
        $positive = $change >= 0;

        return [
            'value' => sprintf('%s%.1f%%', $positive ? '+' : '', $change),
            'label' => $positive ? 'Up' : 'Down',
            'positive' => $positive,
        ];
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

    private function cached(string $key, \Closure $callback, int $seconds = 300): mixed
    {
        return Cache::remember($key, $seconds, $callback);
    }

    /**
     * @return array{\Carbon\Carbon, \Carbon\Carbon}
     */
    private function resolvePeriodBounds(string $period): array
    {
        return match ($period) {
            'this-week' => [now()->startOfWeek(), now()->endOfWeek()],
            'this-month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last-30-days' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            default => [now()->startOfWeek(), now()->endOfWeek()],
        };
    }
}
