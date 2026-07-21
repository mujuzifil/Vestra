<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderStatusService
{
    private array $validTransitions = [
        OrderStatus::PENDING->value => [OrderStatus::PAID->value, OrderStatus::CANCELLED->value],
        OrderStatus::PAID->value => [OrderStatus::PROCESSING->value, OrderStatus::REFUNDED->value],
        OrderStatus::PROCESSING->value => [OrderStatus::PACKED->value, OrderStatus::CANCELLED->value, OrderStatus::REFUNDED->value],
        OrderStatus::PACKED->value => [OrderStatus::SHIPPED->value, OrderStatus::CANCELLED->value],
        OrderStatus::SHIPPED->value => [OrderStatus::DELIVERED->value],
        OrderStatus::DELIVERED->value => [OrderStatus::REFUNDED->value],
        OrderStatus::CANCELLED->value => [],
        OrderStatus::REFUNDED->value => [],
    ];

    public function canTransition(Order $order, OrderStatus $toStatus): bool
    {
        $current = $order->status;
        $allowed = $this->validTransitions[$current] ?? [];
        return in_array($toStatus->value, $allowed, true);
    }

    public function transition(Order $order, OrderStatus $toStatus, ?string $notes = null, ?int $changedBy = null): bool
    {
        if (! $this->canTransition($order, $toStatus)) {
            return false;
        }

        return DB::transaction(function () use ($order, $toStatus, $notes, $changedBy) {
            $order->update(['status' => $toStatus->value]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $toStatus->value,
                'notes' => $notes,
                'changed_by' => $changedBy,
            ]);

            // Restore stock on cancel/refund
            if (in_array($toStatus, [OrderStatus::CANCELLED, OrderStatus::REFUNDED], true)) {
                $this->restoreStock($order);
            }

            // Set timestamps
            if ($toStatus === OrderStatus::SHIPPED) {
                $order->update(['dispatched_at' => now()]);
            }
            if ($toStatus === OrderStatus::DELIVERED) {
                $order->update(['delivered_at' => now()]);
            }

            return true;
        });
    }

    private function restoreStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('stock_quantity', $item->quantity);
            }
        }
    }
}
