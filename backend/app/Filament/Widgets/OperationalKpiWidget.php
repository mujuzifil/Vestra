<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Cache;

class OperationalKpiWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $pendingOrders = Cache::remember('admin.operational.pending_orders', 300, function (): int {
            return Order::query()->where('status', OrderStatus::PENDING->value)->count();
        });

        $awaitingPayment = Cache::remember('admin.operational.awaiting_payment', 300, function (): int {
            return Order::query()->where('payment_status', PaymentStatus::PENDING->value)->count();
        });

        $lowStockProducts = Cache::remember('admin.operational.low_stock_products', 300, function (): int {
            return Product::lowStockCount();
        });

        $newMessages = Cache::remember('admin.operational.new_messages', 300, function (): int {
            return ContactMessage::newCount();
        });

        $reviewsPending = Cache::remember('admin.operational.reviews_pending', 300, function (): int {
            return Review::pendingModerationCount();
        });

        return [
            StatsOverviewWidget\Stat::make('Pending Orders', $pendingOrders)
                ->description('Awaiting confirmation or payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color($this->alertColor($pendingOrders, 5)),

            StatsOverviewWidget\Stat::make('Awaiting Payment', $awaitingPayment)
                ->description('Orders with unpaid invoices')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color($this->alertColor($awaitingPayment, 3)),

            StatsOverviewWidget\Stat::make('Low Stock Products', $lowStockProducts)
                ->description('Items at or below 10 units')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($this->alertColor($lowStockProducts, 0, true)),

            StatsOverviewWidget\Stat::make('New Messages', $newMessages)
                ->description('Unread contact messages')
                ->descriptionIcon('heroicon-m-envelope')
                ->color($this->alertColor($newMessages, 0)),

            StatsOverviewWidget\Stat::make('Reviews to Moderate', $reviewsPending)
                ->description('Pending review approvals')
                ->descriptionIcon('heroicon-m-star')
                ->color($this->alertColor($reviewsPending, 0)),
        ];
    }

    private function alertColor(int $value, int $threshold, bool $critical = false): string
    {
        if ($value === 0) {
            return 'gray';
        }

        if ($critical || $value > $threshold) {
            return 'danger';
        }

        return 'warning';
    }
}
