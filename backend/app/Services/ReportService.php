<?php

namespace App\Services;

use App\Enums\DistributorStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\CreditAccount;
use App\Models\CustomerFeedback;
use App\Models\Distributor;
use App\Models\DistributorRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductWarehouseStock;
use App\Models\Review;
use App\Models\SavedItem;
use App\Models\SearchAnalytic;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Wishlist;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
    // Executive Summary
    // =========================================================

    public function executiveSummary(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $key = $this->cacheKey('executive.summary', $start, $end);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end): array {
            $revenue = $this->revenueSummary($start, $end);
            $sales = $this->salesSummary($start, $end);
            $customers = $this->customerSummary();
            $inventory = $this->inventorySummary();
            $engagement = $this->engagementSummary();
            $distributors = $this->distributorSummary();

            $totalCustomers = $customers['total_customers'];
            $repeatCustomers = $customers['repeat_customers'];
            $repeatPercentage = $totalCustomers > 0 ? round(($repeatCustomers / $totalCustomers) * 100, 2) : 0;

            $creditUtilized = CreditAccount::query()
                ->select(DB::raw('SUM(balance + authorized_amount) as utilized'))
                ->value('utilized') ?? 0;
            $totalCreditLimit = CreditAccount::query()->sum('limit') ?? 0;

            $totalInvoiced = Order::query()->whereBetween('created_at', [$start, $end])->sum('total_amount');
            $totalPaid = PaymentTransaction::query()
                ->where('status', 'success')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount');
            $totalOverdue = Order::query()
                ->whereIn('payment_status', [PaymentStatus::PENDING->value, PaymentStatus::FAILED->value])
                ->where('created_at', '<=', now()->subDays(7))
                ->sum('total_amount');

            return [
                'revenue' => $revenue,
                'sales' => $sales,
                'customers' => array_merge($customers, ['repeat_customer_percentage' => $repeatPercentage]),
                'inventory' => $inventory,
                'engagement' => $engagement,
                'distributors' => $distributors,
                'finance' => [
                    'total_invoiced' => (float) $totalInvoiced,
                    'total_paid' => (float) $totalPaid,
                    'total_overdue' => (float) $totalOverdue,
                    'outstanding_credit' => (float) $creditUtilized,
                    'total_credit_limit' => (float) $totalCreditLimit,
                ],
                'period' => [
                    'start' => Carbon::parse($start)->toDateTimeString(),
                    'end' => Carbon::parse($end)->toDateTimeString(),
                ],
            ];
        });
    }

    // =========================================================
    // Customer Intelligence
    // =========================================================

    public function customerSegments(): array
    {
        $key = 'reports.customer.segments';

        return Cache::remember($key, self::CACHE_TTL, function (): array {
            $customers = User::query()->where('is_admin', false)->get();

            $vip = 0;
            $loyal = 0;
            $new = 0;
            $atRisk = 0;
            $lost = 0;

            foreach ($customers as $customer) {
                $spend = $customer->lifetimeSpend();
                $lastOrder = $customer->lastOrderAt();
                $orders = $customer->lifetimeOrderCount();

                if ($spend >= 500_000 || $orders >= 5) {
                    $vip++;
                } elseif ($orders >= 2 && $lastOrder && $lastOrder->gte(now()->subDays(90))) {
                    $loyal++;
                } elseif ($orders === 0 || ($lastOrder && $lastOrder->gte(now()->subDays(30)))) {
                    $new++;
                } elseif ($lastOrder && $lastOrder->between(now()->subDays(180), now()->subDays(90))) {
                    $atRisk++;
                } else {
                    $lost++;
                }
            }

            return [
                ['segment' => 'VIP', 'count' => $vip, 'color' => 'success'],
                ['segment' => 'Loyal', 'count' => $loyal, 'color' => 'primary'],
                ['segment' => 'New', 'count' => $new, 'color' => 'info'],
                ['segment' => 'At Risk', 'count' => $atRisk, 'color' => 'warning'],
                ['segment' => 'Lost', 'count' => $lost, 'color' => 'danger'],
            ];
        });
    }

    public function customerLifetimeValue(int $limit = 20): array
    {
        $key = "reports.customer.clv.{$limit}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($limit): array {
            return User::query()
                ->where('is_admin', false)
                ->withCount(['orders as paid_orders_count' => fn (Builder $q) => $q->paid()])
                ->withSum(['orders as paid_orders_total' => fn (Builder $q) => $q->paid()], 'total_amount')
                ->orderByDesc('paid_orders_total')
                ->limit($limit)
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'orders' => (int) $user->paid_orders_count,
                    'clv' => (float) ($user->paid_orders_total ?? 0),
                    'aov' => $user->averageOrderValue(),
                    'last_order_at' => $user->lastOrderAt()?->toDateTimeString(),
                ])
                ->toArray();
        });
    }

    public function customerRetentionRate(DateTimeInterface $start, DateTimeInterface $end): float
    {
        $key = $this->cacheKey('customer.retention', $start, $end);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end): float {
            $periodLength = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
            $previousStart = Carbon::parse($start)->subDays($periodLength)->startOfDay();
            $previousEnd = Carbon::parse($start)->subDay()->endOfDay();

            $previousCustomers = User::query()
                ->where('is_admin', false)
                ->whereHas('orders', fn (Builder $q) => $q->paid()->whereBetween('created_at', [$previousStart, $previousEnd]))
                ->pluck('id')
                ->toArray();

            if (empty($previousCustomers)) {
                return 0.0;
            }

            $retained = User::query()
                ->whereIn('id', $previousCustomers)
                ->whereHas('orders', fn (Builder $q) => $q->paid()->whereBetween('created_at', [$start, $end]))
                ->count();

            return round(($retained / count($previousCustomers)) * 100, 2);
        });
    }

    public function customerChurnRate(int $days = 90): float
    {
        $key = "reports.customer.churn.{$days}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days): float {
            $total = User::query()->where('is_admin', false)->count();
            if ($total === 0) {
                return 0.0;
            }

            $churned = User::query()
                ->where('is_admin', false)
                ->whereHas('orders', fn (Builder $q) => $q->paid())
                ->whereDoesntHave('orders', fn (Builder $q) => $q->where('created_at', '>=', now()->subDays($days)))
                ->count();

            return round(($churned / $total) * 100, 2);
        });
    }

    public function topCustomerRegions(int $limit = 10): array
    {
        $key = "reports.customer.regions.{$limit}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($limit): array {
            return DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->join('customer_addresses', 'users.id', '=', 'customer_addresses.user_id')
                ->whereNotNull('customer_addresses.country')
                ->whereIn('orders.payment_status', [PaymentStatus::PAID->value, PaymentStatus::PENDING->value])
                ->select('customer_addresses.country', DB::raw('COUNT(DISTINCT orders.id) as orders'), DB::raw('SUM(orders.total_amount) as revenue'))
                ->groupBy('customer_addresses.country')
                ->orderByDesc('revenue')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => [
                    'country' => $row->country,
                    'orders' => (int) $row->orders,
                    'revenue' => (float) $row->revenue,
                ])
                ->toArray();
        });
    }

    public function customerActivityTimeline(int $limit = 50): array
    {
        $key = "reports.customer.activity.{$limit}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($limit): array {
            $orders = Order::query()
                ->with('user')
                ->latest()
                ->limit($limit)
                ->get()
                ->map(fn (Order $order) => [
                    'type' => 'order',
                    'customer' => $order->user?->name ?? 'Guest',
                    'description' => "Order {$order->invoice_number} — UGX " . number_format($order->total_amount, 2),
                    'created_at' => $order->created_at?->toDateTimeString(),
                ]);

            $registrations = User::query()
                ->where('is_admin', false)
                ->latest()
                ->limit($limit)
                ->get()
                ->map(fn (User $user) => [
                    'type' => 'registration',
                    'customer' => $user->name,
                    'description' => 'New customer registered',
                    'created_at' => $user->created_at?->toDateTimeString(),
                ]);

            return $orders->merge($registrations)
                ->sortByDesc('created_at')
                ->values()
                ->take($limit)
                ->toArray();
        });
    }

    // =========================================================
    // Distributor Intelligence
    // =========================================================

    public function distributorRevenue(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $key = $this->cacheKey('distributor.revenue', $start, $end);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end): array {
            return Distributor::query()
                ->with('user')
                ->withSum(['orders as period_revenue' => fn ($q) => $q->paid()->whereBetween('created_at', [$start, $end])], 'total_amount')
                ->withCount(['orders as period_orders' => fn ($q) => $q->paid()->whereBetween('created_at', [$start, $end])])
                ->orderByDesc('period_revenue')
                ->get()
                ->map(fn (Distributor $distributor) => [
                    'id' => $distributor->id,
                    'company' => $distributor->company_name,
                    'email' => $distributor->email,
                    'revenue' => (float) ($distributor->period_revenue ?? 0),
                    'orders' => (int) ($distributor->period_orders ?? 0),
                ])
                ->toArray();
        });
    }

    public function distributorOrders(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $key = $this->cacheKey('distributor.orders', $start, $end);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end): array {
            $labels = [];
            $orders = [];
            $revenue = [];

            $current = Carbon::parse($start)->copy();
            $endCarbon = Carbon::parse($end);

            while ($current <= $endCarbon) {
                $dayStart = $current->copy()->startOfDay();
                $dayEnd = $current->copy()->endOfDay();

                $summary = Order::query()
                    ->whereNotNull('distributor_id')
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->select(DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
                    ->first();

                $labels[] = $current->format('M d');
                $orders[] = (int) ($summary->count ?? 0);
                $revenue[] = (float) ($summary->total ?? 0);

                $current->addDay();
            }

            return ['labels' => $labels, 'orders' => $orders, 'revenue' => $revenue];
        });
    }

    public function distributorCreditUtilization(): array
    {
        $key = 'reports.distributor.credit_utilization';

        return Cache::remember($key, self::CACHE_TTL, function (): array {
            return CreditAccount::query()
                ->with('distributor')
                ->orderByDesc(DB::raw('balance + authorized_amount'))
                ->get()
                ->map(fn ($account) => [
                    'distributor' => $account->distributor?->company_name ?? 'Unknown',
                    'limit' => (float) $account->limit,
                    'balance' => (float) $account->balance,
                    'authorized' => (float) $account->authorized_amount,
                    'available' => $account->availableCredit(),
                    'utilization_percentage' => $account->utilizationPercentage(),
                ])
                ->toArray();
        });
    }

    public function distributorOutstandingBalances(): array
    {
        $key = 'reports.distributor.outstanding_balances';

        return Cache::remember($key, self::CACHE_TTL, function (): array {
            $totalOutstanding = Order::query()
                ->whereNotNull('distributor_id')
                ->whereIn('payment_status', [PaymentStatus::PENDING->value, PaymentStatus::FAILED->value])
                ->sum('total_amount');

            $byDistributor = Order::query()
                ->whereNotNull('distributor_id')
                ->whereIn('payment_status', [PaymentStatus::PENDING->value, PaymentStatus::FAILED->value])
                ->select('distributor_id', DB::raw('SUM(total_amount) as outstanding'))
                ->groupBy('distributor_id')
                ->orderByDesc('outstanding')
                ->with('distributor')
                ->get()
                ->map(fn ($row) => [
                    'distributor' => $row->distributor?->company_name ?? 'Unknown',
                    'outstanding' => (float) $row->outstanding,
                ]);

            return [
                'total_outstanding' => (float) $totalOutstanding,
                'by_distributor' => $byDistributor->toArray(),
            ];
        });
    }

    public function distributorPerformanceTrend(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $key = $this->cacheKey('distributor.performance_trend', $start, $end);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end): array {
            $orders = $this->distributorOrders($start, $end);
            $applications = $this->distributorTrend($start, $end);

            return [
                'labels' => $orders['labels'],
                'orders' => $orders['orders'],
                'revenue' => $orders['revenue'],
                'applications' => $applications['applications'],
            ];
        });
    }

    // =========================================================
    // Inventory Intelligence
    // =========================================================

    public function inventoryTurnover(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $key = $this->cacheKey('inventory.turnover', $start, $end);

        return Cache::remember($key, self::CACHE_TTL, function () use ($start, $end): array {
            $products = Product::query()
                ->where('stock_quantity', '>', 0)
                ->withSum(['orderItems as sold' => fn ($q) => $q->whereHas('order', fn ($oq) => $oq->paid()->whereBetween('created_at', [$start, $end]))], 'quantity')
                ->get();

            return $products->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'stock_quantity' => $product->stock_quantity,
                'sold' => (int) ($product->sold ?? 0),
                'turnover_ratio' => $product->stock_quantity > 0 ? round(($product->sold ?? 0) / $product->stock_quantity, 2) : 0,
            ])
                ->sortByDesc('turnover_ratio')
                ->take(20)
                ->values()
                ->toArray();
        });
    }

    public function stockValuationByCategory(): array
    {
        $key = 'reports.inventory.valuation_by_category';

        return Cache::remember($key, self::CACHE_TTL, function (): array {
            return Product::query()
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select('categories.name as category', DB::raw('SUM(products.stock_quantity * products.price) as value'), DB::raw('SUM(products.stock_quantity) as units'))
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('value')
                ->get()
                ->map(fn ($row) => [
                    'category' => $row->category,
                    'value' => (float) $row->value,
                    'units' => (int) $row->units,
                ])
                ->toArray();
        });
    }

    public function warehouseUtilization(): array
    {
        $key = 'reports.inventory.warehouse_utilization';

        return Cache::remember($key, self::CACHE_TTL, function (): array {
            return Warehouse::query()
                ->withSum('stocks as total_quantity', 'quantity')
                ->withSum('stocks as total_reserved', 'reserved_quantity')
                ->get()
                ->map(fn (Warehouse $warehouse) => [
                    'warehouse' => $warehouse->name,
                    'code' => $warehouse->code,
                    'total_quantity' => (int) ($warehouse->total_quantity ?? 0),
                    'total_reserved' => (int) ($warehouse->total_reserved ?? 0),
                    'available' => max(0, (int) ($warehouse->total_quantity ?? 0) - (int) ($warehouse->total_reserved ?? 0)),
                    'is_active' => $warehouse->is_active,
                ])
                ->toArray();
        });
    }

    public function deadStock(int $days = 90, int $limit = 20): array
    {
        $key = "reports.inventory.dead_stock.{$days}.{$limit}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days, $limit): array {
            return Product::query()
                ->where('stock_quantity', '>', 0)
                ->whereDoesntHave('orderItems', fn (Builder $q) => $q->where('created_at', '>=', now()->subDays($days)))
                ->orderByDesc('stock_quantity')
                ->limit($limit)
                ->get()
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock_quantity' => $product->stock_quantity,
                    'stock_value' => (float) ($product->stock_quantity * $product->price),
                ])
                ->toArray();
        });
    }

    // =========================================================
    // Search & Engagement Intelligence
    // =========================================================

    public function searchConversionMetrics(int $days = 30): array
    {
        $key = "reports.search.conversion.{$days}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days): array {
            $total = SearchAnalytic::query()->where('searched_at', '>=', now()->subDays($days))->count();
            $zeroResults = SearchAnalytic::query()->where('searched_at', '>=', now()->subDays($days))->where('results_count', 0)->count();
            $clicks = SearchAnalytic::query()->where('searched_at', '>=', now()->subDays($days))->whereNotNull('clicked_product_id')->count();
            $conversions = SearchAnalytic::query()->where('searched_at', '>=', now()->subDays($days))->where('converted', true)->count();
            $uniqueTerms = SearchAnalytic::query()->where('searched_at', '>=', now()->subDays($days))->distinct('term')->count('term');

            return [
                'total_searches' => (int) $total,
                'unique_terms' => (int) $uniqueTerms,
                'zero_result_searches' => (int) $zeroResults,
                'zero_result_rate' => $total > 0 ? round(($zeroResults / $total) * 100, 2) : 0,
                'clicks' => (int) $clicks,
                'click_through_rate' => $total > 0 ? round(($clicks / $total) * 100, 2) : 0,
                'conversions' => (int) $conversions,
                'conversion_rate' => $total > 0 ? round(($conversions / $total) * 100, 2) : 0,
            ];
        });
    }

    public function reviewAnalytics(int $days = 30): array
    {
        $key = "reports.engagement.review_analytics.{$days}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days): array {
            $base = Review::query()->where('created_at', '>=', now()->subDays($days));

            return [
                'total_in_period' => (int) $base->clone()->count(),
                'approved_in_period' => (int) $base->clone()->where('status', ReviewStatus::APPROVED->value)->count(),
                'pending_in_period' => (int) $base->clone()->where('status', ReviewStatus::PENDING->value)->count(),
                'average_rating_in_period' => (float) ($base->clone()->where('status', ReviewStatus::APPROVED->value)->avg('rating') ?? 0),
                'helpful_votes' => (int) $base->clone()->sum('helpful_count'),
                'reported_reviews' => (int) $base->clone()->where('reported_count', '>', 0)->count(),
            ];
        });
    }

    public function wishlistAnalytics(): array
    {
        $key = 'reports.engagement.wishlist';

        return Cache::remember($key, self::CACHE_TTL, function (): array {
            $totalItems = Wishlist::query()->count();
            $uniqueUsers = Wishlist::query()->distinct('user_id')->count('user_id');

            $topProducts = Wishlist::query()
                ->select('product_id', DB::raw('COUNT(*) as count'))
                ->with('product:id,name,sku,price')
                ->groupBy('product_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(fn ($row) => [
                    'product' => $row->product?->name ?? 'Unknown',
                    'sku' => $row->product?->sku ?? '—',
                    'count' => (int) $row->count,
                ]);

            return [
                'total_items' => (int) $totalItems,
                'unique_users' => (int) $uniqueUsers,
                'top_products' => $topProducts->toArray(),
            ];
        });
    }

    public function recommendationEffectiveness(int $days = 30): array
    {
        $key = "reports.engagement.recommendations.{$days}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days): array {
            $orders = Order::query()
                ->paid()
                ->where('created_at', '>=', now()->subDays($days))
                ->count();

            return [
                'note' => 'Recommendation attribution requires a recommendation_source column on order_items. Returning overall paid orders as baseline.',
                'baseline_orders' => (int) $orders,
                'attributed_orders' => 0,
                'attribution_rate' => 0.0,
            ];
        });
    }

    // =========================================================
    // Operational Monitoring
    // =========================================================

    public function queueHealth(): array
    {
        $failedLast24h = DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count();
        $pendingJobs = DB::table('jobs')->count();

        return [
            'failed_jobs_last_24h' => (int) $failedLast24h,
            'pending_jobs' => (int) $pendingJobs,
            'status' => $failedLast24h > 10 || $pendingJobs > 1000 ? 'warning' : 'healthy',
        ];
    }

    public function recentFailedJobs(int $limit = 10): array
    {
        return DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit($limit)
            ->get()
            ->map(fn ($job) => [
                'id' => $job->id,
                'connection' => $job->connection,
                'queue' => $job->queue,
                'exception' => Str::limit($job->exception, 200),
                'failed_at' => $job->failed_at,
            ])
            ->toArray();
    }

    public function schedulerStatus(): array
    {
        $lastRun = cache('schedule:last_run');

        return [
            'last_run_at' => $lastRun,
            'healthy' => $lastRun !== null && Carbon::parse($lastRun)->gte(now()->subHour()),
        ];
    }

    public function storageStatus(): array
    {
        $publicPath = storage_path('app/public');
        $free = @disk_free_space($publicPath);
        $total = @disk_total_space($publicPath);

        return [
            'path' => $publicPath,
            'free_bytes' => $free !== false ? (int) $free : 0,
            'total_bytes' => $total !== false ? (int) $total : 0,
            'used_percentage' => ($total && $free) ? round((($total - $free) / $total) * 100, 2) : 0,
        ];
    }

    public function cacheStatus(): array
    {
        $prefix = config('cache.prefix');

        return [
            'driver' => config('cache.default'),
            'prefix' => $prefix,
            'reachable' => Cache::store()->get("__health_{$prefix}") !== null || Cache::store()->put("__health_{$prefix}", true, 60),
        ];
    }

    public function notificationDeliveryMetrics(int $days = 30): array
    {
        $key = "reports.operational.notifications.{$days}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($days): array {
            $base = DB::table('notifications')->where('created_at', '>=', now()->subDays($days));

            $total = (int) $base->clone()->count();
            $read = (int) $base->clone()->whereNotNull('read_at')->count();

            return [
                'total_notifications' => $total,
                'read_notifications' => $read,
                'read_rate' => $total > 0 ? round(($read / $total) * 100, 2) : 0,
            ];
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
