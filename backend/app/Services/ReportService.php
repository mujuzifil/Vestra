<?php

namespace App\Services;

use App\Enums\DistributorStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\CustomerFeedback;
use App\Models\DistributorRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Cache TTL for report aggregates in seconds.
     */
    private const CACHE_TTL = 3600;

    /**
     * Build a deterministic cache key for the given parameters.
     */
    private function cacheKey(string $name, DateTimeInterface $start, DateTimeInterface $end, array $filters = []): string
    {
        $filterHash = md5(json_encode($filters));

        return sprintf(
            'reports.%s.%s.%s.%s',
            $name,
            $start->format('Y-m-d-H-i-s'),
            $end->format('Y-m-d-H-i-s'),
            $filterHash
        );
    }

    /**
     * Get the previous period boundaries for comparison.
     *
     * @return array{start: Carbon, end: Carbon}
     */
    private function previousPeriod(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $startCarbon = Carbon::parse($start);
        $endCarbon = Carbon::parse($end);
        $diffInSeconds = $startCarbon->diffInSeconds($endCarbon);

        return [
            'start' => $startCarbon->copy()->subSeconds($diffInSeconds + 1)->startOfSecond(),
            'end' => $startCarbon->copy()->subSecond(),
        ];
    }

    private function paidOrderQuery(?DateTimeInterface $start = null, ?DateTimeInterface $end = null): Builder
    {
        $query = Order::query()
            ->whereIn('payment_status', [PaymentStatus::PAID->value, PaymentStatus::PENDING->value]);

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        return $query;
    }

    // =========================================================
    // Revenue
    // =========================================================

    public function revenueSummary(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $key = $this->cacheKey('revenue.summary', $start, $end);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end): array {
            $currentRevenue = $this->paidOrderQuery($start, $end)->sum('total_amount');
            $currentOrders = $this->paidOrderQuery($start, $end)->count();
            $aov = $currentOrders > 0 ? $currentRevenue / $currentOrders : 0;

            $previous = $this->previousPeriod($start, $end);
            $previousRevenue = $this->paidOrderQuery($previous['start'], $previous['end'])->sum('total_amount');
            $change = $this->calculateChange((float) $currentRevenue, (float) $previousRevenue);

            return [
                'total_revenue' => (float) $currentRevenue,
                'previous_period_revenue' => (float) $previousRevenue,
                'change_percentage' => $change,
                'order_count' => (int) $currentOrders,
                'average_order_value' => (float) $aov,
            ];
        });
    }

    public function revenueTrend(DateTimeInterface $start, DateTimeInterface $end, string $granularity = 'day'): array
    {
        $key = $this->cacheKey('revenue.trend', $start, $end, ['granularity' => $granularity]);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end, $granularity): array {
            $format = match ($granularity) {
                'week' => '%Y-%u',
                'month' => '%Y-%m',
                'year' => '%Y',
                default => '%Y-%m-%d',
            };

            $labelFormat = match ($granularity) {
                'week' => 'Y-\WW',
                'month' => 'M Y',
                'year' => 'Y',
                default => 'M d',
            };

            $results = $this->paidOrderQuery($start, $end)
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '{$format}') as period"),
                    DB::raw('SUM(total_amount) as revenue'),
                    DB::raw('COUNT(*) as orders')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->keyBy('period');

            $labels = [];
            $revenue = [];
            $orders = [];

            $current = Carbon::parse($start)->copy();
            $endCarbon = Carbon::parse($end);

            while ($current <= $endCarbon) {
                $periodKey = match ($granularity) {
                    'week' => $current->format('Y') . '-' . $current->format('W'),
                    'month' => $current->format('Y-m'),
                    'year' => $current->format('Y'),
                    default => $current->format('Y-m-d'),
                };

                $labels[] = $current->format($labelFormat);
                $revenue[] = (float) ($results[$periodKey]?->revenue ?? 0);
                $orders[] = (int) ($results[$periodKey]?->orders ?? 0);

                $current = match ($granularity) {
                    'week' => $current->addWeek(),
                    'month' => $current->addMonth(),
                    'year' => $current->addYear(),
                    default => $current->addDay(),
                };
            }

            return [
                'labels' => $labels,
                'revenue' => $revenue,
                'orders' => $orders,
            ];
        });
    }

    public function revenueByPaymentMethod(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $key = $this->cacheKey('revenue.payment_method', $start, $end);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end): array {
            return $this->paidOrderQuery($start, $end)
                ->select('payment_method', DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as orders'))
                ->groupBy('payment_method')
                ->orderByDesc('revenue')
                ->get()
                ->map(fn ($row) => [
                    'method' => $row->paymentMethodLabel(),
                    'revenue' => (float) $row->revenue,
                    'orders' => (int) $row->orders,
                ])
                ->toArray();
        });
    }

    public function revenueByOrderStatus(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $key = $this->cacheKey('revenue.order_status', $start, $end);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end): array {
            $results = Order::query()
                ->whereBetween('created_at', [$start, $end])
                ->select('status', DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as orders'))
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            $data = [];
            foreach (OrderStatus::cases() as $case) {
                $data[] = [
                    'status' => $case->label(),
                    'revenue' => (float) ($results[$case->value]?->revenue ?? 0),
                    'orders' => (int) ($results[$case->value]?->orders ?? 0),
                ];
            }

            return $data;
        });
    }

    // =========================================================
    // Sales
    // =========================================================

    public function salesSummary(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $key = $this->cacheKey('sales.summary', $start, $end);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end): array {
            $paidOrders = $this->paidOrderQuery($start, $end)->count();
            $totalOrders = Order::query()->whereBetween('created_at', [$start, $end])->count();
            $cancelled = Order::query()->whereBetween('created_at', [$start, $end])->where('status', OrderStatus::CANCELLED->value)->count();
            $refunded = Order::query()->whereBetween('created_at', [$start, $end])->where('payment_status', PaymentStatus::REFUNDED->value)->count();
            $unitsSold = (int) OrderItem::query()
                ->whereHas('order', fn (Builder $q) => $q->whereBetween('created_at', [$start, $end])->whereIn('payment_status', [PaymentStatus::PAID->value, PaymentStatus::PENDING->value]))
                ->sum('quantity');

            return [
                'paid_orders' => (int) $paidOrders,
                'total_orders' => (int) $totalOrders,
                'cancelled_orders' => (int) $cancelled,
                'refunded_orders' => (int) $refunded,
                'units_sold' => $unitsSold,
            ];
        });
    }

    public function ordersTrend(DateTimeInterface $start, DateTimeInterface $end, string $granularity = 'day'): array
    {
        $key = $this->cacheKey('orders.trend', $start, $end, ['granularity' => $granularity]);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end, $granularity): array {
            $trend = $this->revenueTrend($start, $end, $granularity);

            return [
                'labels' => $trend['labels'],
                'orders' => $trend['orders'],
            ];
        });
    }

    public function bestSellers(DateTimeInterface $start, DateTimeInterface $end, int $limit = 10): array
    {
        $key = $this->cacheKey('sales.best_sellers', $start, $end, ['limit' => $limit]);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end, $limit): array {
            return DB::table('order_items')
                ->select(
                    'product_id',
                    'product_name',
                    'product_sku',
                    DB::raw('SUM(quantity) as total_sold'),
                    DB::raw('SUM(line_total) as total_revenue')
                )
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('orders.payment_status', [PaymentStatus::PAID->value, PaymentStatus::PENDING->value])
                ->whereBetween('orders.created_at', [$start, $end])
                ->groupBy('product_id', 'product_name', 'product_sku')
                ->orderByDesc('total_sold')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => [
                    'product_id' => $row->product_id,
                    'product_name' => $row->product_name,
                    'product_sku' => $row->product_sku,
                    'total_sold' => (int) $row->total_sold,
                    'total_revenue' => (float) $row->total_revenue,
                ])
                ->toArray();
        });
    }

    public function worstPerformers(DateTimeInterface $start, DateTimeInterface $end, int $limit = 10): array
    {
        $key = $this->cacheKey('sales.worst_performers', $start, $end, ['limit' => $limit]);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end, $limit): array {
            $sellingIds = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('orders.payment_status', [PaymentStatus::PAID->value, PaymentStatus::PENDING->value])
                ->whereBetween('orders.created_at', [$start, $end])
                ->select('product_id')
                ->groupBy('product_id')
                ->pluck('product_id');

            return Product::query()
                ->when($sellingIds->isNotEmpty(), fn (Builder $q) => $q->whereNotIn('id', $sellingIds))
                ->orderBy('stock_quantity', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn (Product $product) => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'stock_quantity' => $product->stock_quantity,
                    'price' => (float) $product->price,
                ])
                ->toArray();
        });
    }

    public function salesByCategory(DateTimeInterface $start, DateTimeInterface $end, int $limit = 10): array
    {
        $key = $this->cacheKey('sales.by_category', $start, $end, ['limit' => $limit]);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end, $limit): array {
            return DB::table('order_items')
                ->select(
                    'categories.name as category_name',
                    DB::raw('SUM(order_items.quantity) as total_sold'),
                    DB::raw('SUM(order_items.line_total) as total_revenue')
                )
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->whereIn('orders.payment_status', [PaymentStatus::PAID->value, PaymentStatus::PENDING->value])
                ->whereBetween('orders.created_at', [$start, $end])
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('total_revenue')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => [
                    'category' => $row->category_name,
                    'total_sold' => (int) $row->total_sold,
                    'total_revenue' => (float) $row->total_revenue,
                ])
                ->toArray();
        });
    }

    public function cancelledOrders(DateTimeInterface $start, DateTimeInterface $end, int $limit = 10): array
    {
        $key = $this->cacheKey('sales.cancelled', $start, $end, ['limit' => $limit]);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end, $limit): array {
            return Order::query()
                ->where('status', OrderStatus::CANCELLED->value)
                ->whereBetween('created_at', [$start, $end])
                ->with('user')
                ->latest()
                ->limit($limit)
                ->get()
                ->map(fn (Order $order) => [
                    'invoice' => $order->invoice_number,
                    'customer' => $order->user?->name ?? 'Guest',
                    'total' => (float) $order->total_amount,
                    'created_at' => $order->created_at?->toDateTimeString(),
                ])
                ->toArray();
        });
    }

    // =========================================================
    // Customers
    // =========================================================

    public function customerSummary(): array
    {
        $key = 'reports.customer.summary';

        return Cache::remember($key, self::CACHE_TTL, function (): array {
            $total = User::query()->where('is_admin', false)->count();
            $newThisMonth = User::query()->where('is_admin', false)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
            $repeat = User::query()->where('is_admin', false)->whereHas('orders', fn (Builder $q) => $q->paid())->count();
            $inactive = User::query()->where('is_admin', false)->where('status', 'inactive')->count();

            return [
                'total_customers' => (int) $total,
                'new_this_month' => (int) $newThisMonth,
                'repeat_customers' => (int) $repeat,
                'inactive_customers' => (int) $inactive,
            ];
        });
    }

    public function customerGrowth(DateTimeInterface $start, DateTimeInterface $end, string $granularity = 'month'): array
    {
        $key = $this->cacheKey('customer.growth', $start, $end, ['granularity' => $granularity]);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end, $granularity): array {
            $format = match ($granularity) {
                'week' => '%Y-%u',
                'month' => '%Y-%m',
                default => '%Y-%m-%d',
            };

            $labelFormat = match ($granularity) {
                'week' => 'Y-\WW',
                'month' => 'M Y',
                default => 'M d',
            };

            $results = User::query()
                ->where('is_admin', false)
                ->whereBetween('created_at', [$start, $end])
                ->select(DB::raw("DATE_FORMAT(created_at, '{$format}') as period"), DB::raw('COUNT(*) as new_customers'))
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->keyBy('period');

            $labels = [];
            $values = [];

            $current = Carbon::parse($start)->copy();
            $endCarbon = Carbon::parse($end);

            while ($current <= $endCarbon) {
                $periodKey = match ($granularity) {
                    'week' => $current->format('Y') . '-' . $current->format('W'),
                    'month' => $current->format('Y-m'),
                    default => $current->format('Y-m-d'),
                };

                $labels[] = $current->format($labelFormat);
                $values[] = (int) ($results[$periodKey]?->new_customers ?? 0);

                $current = match ($granularity) {
                    'week' => $current->addWeek(),
                    'month' => $current->addMonth(),
                    default => $current->addDay(),
                };
            }

            return ['labels' => $labels, 'new_customers' => $values];
        });
    }

    public function topCustomers(DateTimeInterface $start, DateTimeInterface $end, int $limit = 10): array
    {
        $key = $this->cacheKey('customer.top', $start, $end, ['limit' => $limit]);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end, $limit): array {
            return User::query()
                ->where('is_admin', false)
                ->withCount(['orders as paid_orders_count' => fn (Builder $q) => $q->paid()->whereBetween('created_at', [$start, $end])])
                ->withSum(['orders as paid_orders_total' => fn (Builder $q) => $q->paid()->whereBetween('created_at', [$start, $end])], 'total_amount')
                ->orderByDesc('paid_orders_total')
                ->limit($limit)
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'orders' => (int) $user->paid_orders_count,
                    'lifetime_spend' => (float) $user->lifetimeSpend(),
                    'period_spend' => (float) ($user->paid_orders_total ?? 0),
                ])
                ->toArray();
        });
    }

    public function inactiveCustomers(int $days = 90, int $limit = 10): array
    {
        $key = "reports.customer.inactive.{$days}.{$limit}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days, $limit): array {
            return User::query()
                ->where('is_admin', false)
                ->whereDoesntHave('orders', fn (Builder $q) => $q->where('created_at', '>=', now()->subDays($days)))
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'registered_at' => $user->created_at?->toDateTimeString(),
                    'last_order_at' => $user->orders()->latest()->value('created_at'),
                ])
                ->toArray();
        });
    }

    public function averageCustomerValue(): float
    {
        $key = 'reports.customer.average_value';

        return Cache::remember($key, self::CACHE_TTL, function (): float {
            $total = User::query()->where('is_admin', false)->count();
            if ($total === 0) {
                return 0;
            }

            $revenue = Order::query()->paid()->sum('total_amount');

            return (float) ($revenue / $total);
        });
    }

    // =========================================================
    // Inventory
    // =========================================================

    public function inventorySummary(): array
    {
        $key = 'reports.inventory.summary';

        return Cache::remember($key, self::CACHE_TTL, function (): array {
            $result = Product::query()
                ->select(
                    DB::raw('SUM(stock_quantity * price) as total_value'),
                    DB::raw('SUM(stock_quantity) as total_units'),
                    DB::raw('COUNT(*) as product_count'),
                    DB::raw('SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock'),
                    DB::raw('SUM(CASE WHEN stock_quantity > 0 AND stock_quantity <= 10 THEN 1 ELSE 0 END) as low_stock')
                )
                ->first();

            return [
                'total_value' => (float) ($result->total_value ?? 0),
                'total_units' => (int) ($result->total_units ?? 0),
                'product_count' => (int) ($result->product_count ?? 0),
                'out_of_stock' => (int) ($result->out_of_stock ?? 0),
                'low_stock' => (int) ($result->low_stock ?? 0),
            ];
        });
    }

    public function lowStock(int $threshold = 10, int $limit = 10): array
    {
        $key = "reports.inventory.low_stock.{$threshold}.{$limit}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($threshold, $limit): array {
            return Product::query()
                ->where('stock_quantity', '<=', $threshold)
                ->where('stock_quantity', '>', 0)
                ->with('category')
                ->orderBy('stock_quantity')
                ->limit($limit)
                ->get()
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category?->name ?? '—',
                    'stock_quantity' => $product->stock_quantity,
                    'price' => (float) $product->price,
                ])
                ->toArray();
        });
    }

    public function outOfStock(int $limit = 10): array
    {
        $key = "reports.inventory.out_of_stock.{$limit}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($limit): array {
            return Product::query()
                ->where('stock_quantity', '<=', 0)
                ->with('category')
                ->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category?->name ?? '—',
                    'price' => (float) $product->price,
                ])
                ->toArray();
        });
    }

    public function fastMovingProducts(DateTimeInterface $start, DateTimeInterface $end, int $limit = 10): array
    {
        $key = $this->cacheKey('inventory.fast_moving', $start, $end, ['limit' => $limit]);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end, $limit): array {
            return DB::table('order_items')
                ->select(
                    'product_id',
                    'product_name',
                    'product_sku',
                    DB::raw('SUM(quantity) as total_sold')
                )
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('orders.payment_status', [PaymentStatus::PAID->value, PaymentStatus::PENDING->value])
                ->whereBetween('orders.created_at', [$start, $end])
                ->groupBy('product_id', 'product_name', 'product_sku')
                ->orderByDesc('total_sold')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => [
                    'product_id' => $row->product_id,
                    'name' => $row->product_name,
                    'sku' => $row->product_sku,
                    'total_sold' => (int) $row->total_sold,
                ])
                ->toArray();
        });
    }

    public function slowMovingProducts(DateTimeInterface $start, DateTimeInterface $end, int $limit = 10): array
    {
        $key = $this->cacheKey('inventory.slow_moving', $start, $end, ['limit' => $limit]);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end, $limit): array {
            $sellingIds = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->pluck('product_id')
                ->unique()
                ->toArray();

            return Product::query()
                ->whereNotIn('id', $sellingIds)
                ->where('stock_quantity', '>', 0)
                ->orderByDesc('stock_quantity')
                ->limit($limit)
                ->get()
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock_quantity' => $product->stock_quantity,
                ])
                ->toArray();
        });
    }

    public function productsNeverOrdered(int $limit = 10): array
    {
        $key = "reports.inventory.never_ordered.{$limit}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($limit): array {
            return Product::query()
                ->whereDoesntHave('orderItems')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock_quantity' => $product->stock_quantity,
                ])
                ->toArray();
        });
    }

    // =========================================================
    // Engagement
    // =========================================================

    public function engagementSummary(): array
    {
        $key = 'reports.engagement.summary';

        return Cache::remember($key, self::CACHE_TTL, function (): array {
            $totalReviews = Review::query()->count();
            $pendingReviews = Review::query()->pending()->count();
            $averageRating = (float) Review::query()->approved()->avg('rating') ?? 0;
            $totalFeedback = CustomerFeedback::query()->count();
            $unreadFeedback = CustomerFeedback::query()->unread()->count();
            $totalMessages = ContactMessage::query()->count();
            $unreadMessages = ContactMessage::query()->unread()->count();

            return [
                'total_reviews' => (int) $totalReviews,
                'pending_reviews' => (int) $pendingReviews,
                'average_rating' => round($averageRating, 2),
                'total_feedback' => (int) $totalFeedback,
                'unread_feedback' => (int) $unreadFeedback,
                'total_messages' => (int) $totalMessages,
                'unread_messages' => (int) $unreadMessages,
            ];
        });
    }

    public function reviewStatistics(): array
    {
        $key = 'reports.engagement.review_stats';

        return Cache::remember($key, self::CACHE_TTL, function (): array {
            $counts = Review::query()
                ->select('status', DB::raw('COUNT(*) as count'), DB::raw('AVG(rating) as avg_rating'))
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            $byRating = Review::query()
                ->approved()
                ->select('rating', DB::raw('COUNT(*) as count'))
                ->groupBy('rating')
                ->orderBy('rating')
                ->pluck('count', 'rating')
                ->toArray();

            $data = [];
            foreach (ReviewStatus::cases() as $case) {
                $data[$case->value] = [
                    'label' => $case->label(),
                    'count' => (int) ($counts[$case->value]?->count ?? 0),
                    'avg_rating' => (float) ($counts[$case->value]?->avg_rating ?? 0),
                ];
            }

            return [
                'by_status' => $data,
                'by_rating' => $byRating,
            ];
        });
    }

    public function engagementTrend(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $key = $this->cacheKey('engagement.trend', $start, $end);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end): array {
            $labels = [];
            $reviews = [];
            $feedback = [];
            $messages = [];

            $current = Carbon::parse($start)->copy();
            $endCarbon = Carbon::parse($end);

            while ($current <= $endCarbon) {
                $dayStart = $current->copy()->startOfDay();
                $dayEnd = $current->copy()->endOfDay();

                $labels[] = $current->format('M d');
                $reviews[] = Review::query()->whereBetween('created_at', [$dayStart, $dayEnd])->count();
                $feedback[] = CustomerFeedback::query()->whereBetween('created_at', [$dayStart, $dayEnd])->count();
                $messages[] = ContactMessage::query()->whereBetween('created_at', [$dayStart, $dayEnd])->count();

                $current->addDay();
            }

            return [
                'labels' => $labels,
                'reviews' => $reviews,
                'feedback' => $feedback,
                'messages' => $messages,
            ];
        });
    }

    // =========================================================
    // Distributors
    // =========================================================

    public function distributorSummary(): array
    {
        $key = 'reports.distributor.summary';

        return Cache::remember($key, self::CACHE_TTL, function (): array {
            $total = DistributorRequest::query()->count();
            $pending = DistributorRequest::query()->awaitingReview()->count();
            $approved = DistributorRequest::query()->approved()->count();
            $rejected = DistributorRequest::query()->rejected()->count();
            $approvalRate = $total > 0 ? ($approved / $total) * 100 : 0;

            return [
                'total_applications' => (int) $total,
                'pending_review' => (int) $pending,
                'approved' => (int) $approved,
                'rejected' => (int) $rejected,
                'approval_rate' => round($approvalRate, 2),
            ];
        });
    }

    public function applicationsByStatus(): array
    {
        $key = 'reports.distributor.by_status';

        return Cache::remember($key, self::CACHE_TTL, function (): array {
            $counts = DistributorRequest::query()
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $data = [];
            foreach (DistributorStatus::cases() as $case) {
                $data[] = [
                    'status' => $case->label(),
                    'count' => (int) ($counts[$case->value] ?? 0),
                    'color' => $case->color(),
                ];
            }

            return $data;
        });
    }

    public function applicationsByCountry(int $limit = 10): array
    {
        $key = "reports.distributor.by_country.{$limit}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($limit): array {
            return DistributorRequest::query()
                ->select('country', DB::raw('COUNT(*) as count'))
                ->whereNotNull('country')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => [
                    'country' => $row->country ?: 'Unknown',
                    'count' => (int) $row->count,
                ])
                ->toArray();
        });
    }

    public function applicationsByRegion(int $limit = 10): array
    {
        $key = "reports.distributor.by_region.{$limit}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($limit): array {
            return DistributorRequest::query()
                ->select('region', DB::raw('COUNT(*) as count'))
                ->whereNotNull('region')
                ->groupBy('region')
                ->orderByDesc('count')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => [
                    'region' => $row->region ?: 'Unknown',
                    'count' => (int) $row->count,
                ])
                ->toArray();
        });
    }

    public function distributorTrend(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $key = $this->cacheKey('distributor.trend', $start, $end);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end): array {
            $labels = [];
            $values = [];

            $current = Carbon::parse($start)->copy();
            $endCarbon = Carbon::parse($end);

            while ($current <= $endCarbon) {
                $dayStart = $current->copy()->startOfDay();
                $dayEnd = $current->copy()->endOfDay();

                $labels[] = $current->format('M d');
                $values[] = DistributorRequest::query()->whereBetween('created_at', [$dayStart, $dayEnd])->count();

                $current->addDay();
            }

            return ['labels' => $labels, 'applications' => $values];
        });
    }

    // =========================================================
    // Helpers
    // =========================================================

    private function calculateChange(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
