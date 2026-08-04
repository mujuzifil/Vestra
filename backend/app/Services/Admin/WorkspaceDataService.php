<?php

namespace App\Services\Admin;

use App\Enums\DistributorStatus;
use App\Enums\ProductStatus;
use App\Enums\QuoteRequestStatus;
use App\Models\AuditLog;
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
        return Cache::remember('admin.workspace.recent_activity', 300, function (): array {
            $logs = AuditLog::query()
                ->with('user')
                ->whereNotIn('action', ['password_change.required', 'password_changed', 'password_change.bypass_attempt'])
                ->where('action', 'not like', '%login%')
                ->latest()
                ->limit(6)
                ->get();

            return $logs->map(fn (AuditLog $log): array => [
                'id' => $log->id,
                'icon' => $this->activityIcon($log->action),
                'color' => $this->activityColor($log->action),
                'title' => $this->activityTitle($log),
                'subtitle' => $this->activitySubtitle($log),
                'time' => $log->created_at?->diffForHumans() ?? '',
                'url' => $this->activityUrl($log),
            ])->toArray();
        });
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

    private function activityTitle(AuditLog $log): string
    {
        $action = $log->action;
        $subject = $this->subjectName($log);

        return match (true) {
            str_contains($action, 'quote_request') => "New quote request from {$subject}",
            str_contains($action, 'distributor_request') => "Distributor application submitted by {$subject}",
            str_contains($action, 'contact_message') => "New contact message from {$subject}",
            str_contains($action, 'customer') => "New customer {$subject}",
            str_contains($action, 'support_ticket') => "Support ticket {$subject} updated",
            str_contains($action, 'blog_post') => "Blog post \"{$subject}\" published",
            str_contains($action, 'product') => "Product {$subject} updated",
            str_contains($action, 'user') => "User {$subject} updated",
            default => ucfirst(str_replace(['.', '_'], ' ', $action)),
        };
    }

    private function activitySubtitle(AuditLog $log): string
    {
        $action = $log->action;
        $identifier = $this->subjectIdentifier($log);
        $actor = $log->user?->name ?? 'System';

        return match (true) {
            str_contains($action, 'quote_request') => "Quote #{$identifier}",
            str_contains($action, 'distributor_request') => "Application #{$identifier}",
            str_contains($action, 'contact_message') => $log->subject?->subject ? "Subject: {$log->subject->subject}" : "From {$actor}",
            str_contains($action, 'support_ticket') => "by {$actor}",
            str_contains($action, 'blog_post') => "by {$actor}",
            default => "by {$actor}",
        };
    }

    private function subjectName(AuditLog $log): string
    {
        $subject = $log->subject;

        if (! $subject) {
            return '#' . $log->subject_id;
        }

        return $subject->company_name
            ?? $subject->full_name
            ?? $subject->name
            ?? $subject->title
            ?? $subject->reference_number
            ?? ('#' . $log->subject_id);
    }

    private function subjectIdentifier(AuditLog $log): string
    {
        return $log->subject?->reference_number
            ?? $log->subject?->invoice_number
            ?? (string) $log->subject_id;
    }

    private function activityUrl(AuditLog $log): ?string
    {
        $subject = $log->subject;

        if (! $subject) {
            return null;
        }

        $resource = match (class_basename($log->subject_type)) {
            'QuoteRequest' => 'sales/quotes',
            'DistributorRequest' => 'distributors/applications',
            'ContactMessage' => 'contact-messages',
            'CustomerFeedback' => 'customer-feedback',
            'BlogPost' => 'blog-posts',
            'Product' => 'products',
            'User' => 'users',
            default => null,
        };

        return $resource ? url("/{$resource}") : null;
    }

    private function activityIcon(string $action): string
    {
        return match (true) {
            str_contains($action, 'quote_request') => 'heroicon-o-document-text',
            str_contains($action, 'distributor_request') => 'heroicon-o-user-group',
            str_contains($action, 'contact_message') => 'heroicon-o-envelope',
            str_contains($action, 'customer') => 'heroicon-o-users',
            str_contains($action, 'support_ticket') => 'heroicon-o-ticket',
            str_contains($action, 'blog_post') => 'heroicon-o-newspaper',
            str_contains($action, 'product') => 'heroicon-o-cube',
            str_contains($action, 'order') => 'heroicon-o-shopping-cart',
            str_contains($action, 'setting') => 'heroicon-o-cog-6-tooth',
            str_contains($action, 'task') => 'heroicon-o-check-circle',
            default => 'heroicon-o-bolt',
        };
    }

    private function activityColor(string $action): string
    {
        return match (true) {
            str_contains($action, 'delete') => 'danger',
            str_contains($action, 'create') || str_contains($action, 'submitted') => 'success',
            str_contains($action, 'update') || str_contains($action, 'approved') => 'primary',
            str_contains($action, 'login') => 'info',
            default => 'gray',
        };
    }
}
