<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'status',
        'payment_method',
        'payment_status',
        'shipping_address',
        'subtotal',
        'shipping_cost',
        'tax_amount',
        'total_amount',
        'notes',
        'courier',
        'tracking_number',
        'dispatched_at',
        'delivered_at',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'dispatched_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function latestPaymentTransaction(): ?PaymentTransaction
    {
        return $this->paymentTransactions()->latest()->first();
    }

    public function formattedInvoice(): string
    {
        return $this->invoice_number;
    }

    public function itemsCount(): int
    {
        return $this->items()->count();
    }

    public function amountPaid(): float
    {
        return match ($this->payment_status) {
            PaymentStatus::PAID->value => (float) $this->total_amount,
            PaymentStatus::REFUNDED->value => 0.0,
            default => 0.0,
        };
    }

    public function outstandingBalance(): float
    {
        return match ($this->payment_status) {
            PaymentStatus::PAID->value => 0.0,
            PaymentStatus::REFUNDED->value => 0.0,
            default => (float) $this->total_amount,
        };
    }

    public function paymentMethodLabel(): string
    {
        return \App\Enums\PaymentMethod::tryFrom($this->payment_method)?->label() ?? ucfirst($this->payment_method);
    }

    public function timeline(): array
    {
        $events = [];

        $events[] = [
            'icon' => 'heroicon-o-shopping-cart',
            'color' => 'primary',
            'title' => 'Order created',
            'description' => 'Order #' . $this->invoice_number . ' was placed.',
            'time' => $this->created_at,
            'actor' => $this->user?->name ?? 'Customer',
        ];

        if ($this->payment_status === PaymentStatus::PAID->value) {
            $events[] = [
                'icon' => 'heroicon-o-currency-dollar',
                'color' => 'success',
                'title' => 'Payment received',
                'description' => 'Payment of ' . number_format($this->total_amount, 2) . ' UGX received via ' . $this->paymentMethodLabel() . '.',
                'time' => $this->updated_at,
                'actor' => 'System',
            ];
        }

        foreach ($this->statusHistory as $history) {
            $status = OrderStatus::tryFrom($history->status);
            $events[] = [
                'icon' => 'heroicon-o-arrow-path',
                'color' => $status?->color() ?? 'gray',
                'title' => 'Status updated to ' . ($status?->label() ?? $history->status),
                'description' => $history->notes ?: 'Order status changed.',
                'time' => $history->created_at,
                'actor' => $history->changedBy?->name ?? 'System',
            ];
        }

        if ($this->dispatched_at) {
            $events[] = [
                'icon' => 'heroicon-o-truck',
                'color' => 'info',
                'title' => 'Order dispatched',
                'description' => $this->courier ? 'Shipped via ' . $this->courier . '.' : 'Order has been dispatched.',
                'time' => $this->dispatched_at,
                'actor' => 'System',
            ];
        }

        if ($this->delivered_at) {
            $events[] = [
                'icon' => 'heroicon-o-check-circle',
                'color' => 'success',
                'title' => 'Order delivered',
                'description' => 'Order marked as delivered.',
                'time' => $this->delivered_at,
                'actor' => 'System',
            ];
        }

        usort($events, fn (array $a, array $b): int => ($a['time'] ?? now()) <=> ($b['time'] ?? now()));

        return $events;
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatus::PAID->value);
    }

    public function scopeForDateRange(Builder $query, \DateTimeInterface $start, \DateTimeInterface $end): Builder
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public static function paidRevenueBetween(\DateTimeInterface $start, \DateTimeInterface $end): float
    {
        return (float) static::query()
            ->paid()
            ->forDateRange($start, $end)
            ->sum('total_amount');
    }

    /**
     * @return array<string, int>
     */
    public static function countByStatus(): array
    {
        $counts = static::query()
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

    public function scopeForInvoice(Builder $query, string $invoice): Builder
    {
        return $query->where('invoice_number', 'like', '%' . $invoice . '%');
    }

    public function scopeForCustomerName(Builder $query, string $name): Builder
    {
        return $query->whereHas('user', fn (Builder $q) => $q->where('name', 'like', '%' . $name . '%'));
    }

    public function scopeHighValue(Builder $query, float $threshold = 200000): Builder
    {
        return $query->where('total_amount', '>=', $threshold);
    }

    public function scopeRecentlyUpdated(Builder $query, int $days = 7): Builder
    {
        return $query->where('updated_at', '>=', now()->subDays($days));
    }
}
