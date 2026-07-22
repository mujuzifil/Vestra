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
        OrderStatus::PAID->value => [OrderStatus::PROCESSING->value, OrderStatus::CANCELLED->value, OrderStatus::REFUNDED->value],
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
            // Re-fetch with lock to prevent concurrent transitions.
            $lockedOrder = Order::lockForUpdate()->find($order->id);

            if (! $lockedOrder) {
                return false;
            }

            // Double-check after acquiring lock.
            if (! $this->canTransition($lockedOrder, $toStatus)) {
                return false;
            }

            $updates = ['status' => $toStatus->value];

            if ($toStatus === OrderStatus::SHIPPED) {
                $updates['dispatched_at'] = now();
            }
            if ($toStatus === OrderStatus::DELIVERED) {
                $updates['delivered_at'] = now();
            }

            $lockedOrder->update($updates);

            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'status' => $toStatus->value,
                'notes' => $notes,
                'changed_by' => $changedBy,
            ]);

            // Restore stock on cancel/refund, but only if stock was previously decremented.
            if (in_array($toStatus, [OrderStatus::CANCELLED, OrderStatus::REFUNDED], true) && $lockedOrder->stock_decremented) {
                $this->restoreStock($lockedOrder);
                $lockedOrder->update(['stock_decremented' => false]);
            }

            // Refresh the in-memory order so callers see current state.
            $order->refresh();

            return true;
        });
    }

    private function restoreStock(Order $order): void
    {
        $productIds = $order->items->pluck('product_id')->toArray();
        sort($productIds);

        $lockedProducts = Product::lockForUpdate()->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            $product = $lockedProducts->get($item->product_id);
            if ($product) {
                $product->increment('stock_quantity', $item->quantity);
            }
        }
    }
}
