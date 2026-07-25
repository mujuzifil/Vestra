<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\ProductWarehouseStock;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Adjust stock for a product at a warehouse. Positive quantities add stock,
     * negative quantities remove stock. A stock movement record is created.
     */
    public function adjustStock(
        Product $product,
        Warehouse $warehouse,
        int $quantity,
        string $reason,
        ?User $user = null
    ): StockMovement {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $reason, $user) {
            $stock = ProductWarehouseStock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                ['quantity' => 0, 'reserved_quantity' => 0, 'reorder_level' => 0]
            );

            $newQuantity = max(0, $stock->quantity + $quantity);
            $stock->update(['quantity' => $newQuantity]);

            $this->syncProductStock($product);

            return StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => StockMovementType::ADJUSTMENT,
                'quantity' => abs($quantity),
                'balance_after' => $newQuantity,
                'reason' => $reason,
                'reference_type' => null,
                'reference_id' => null,
                'user_id' => $user?->id,
                'notes' => $quantity >= 0 ? 'Manual stock increase' : 'Manual stock decrease',
            ]);
        });
    }

    /**
     * Receive stock against a purchase order item. Updates the item's received
     * quantity, the warehouse stock, and records an IN stock movement.
     */
    public function receivePurchaseOrderItem(PurchaseOrderItem $item, int $quantity, ?User $user = null): void
    {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($item, $quantity, $user) {
            $item->increment('received_quantity', $quantity);

            $purchaseOrder = $item->purchaseOrder;
            $warehouse = $purchaseOrder->warehouse;
            $product = $item->product;

            $stock = ProductWarehouseStock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                ['quantity' => 0, 'reserved_quantity' => 0, 'reorder_level' => 0]
            );

            $stock->increment('quantity', $quantity);
            $stock->refresh();

            $this->syncProductStock($product);

            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => StockMovementType::IN,
                'quantity' => $quantity,
                'balance_after' => $stock->quantity,
                'reason' => 'Purchase order receipt',
                'reference_type' => $item->getMorphClass(),
                'reference_id' => $item->id,
                'user_id' => $user?->id,
                'notes' => "Received {$quantity} units for PO {$purchaseOrder->po_number}",
            ]);
        });
    }

    /**
     * Reserve stock for a product at a warehouse. Returns false if insufficient
     * available stock or if no stock record exists.
     */
    public function reserveStock(Product $product, Warehouse $warehouse, int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $stock = ProductWarehouseStock::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->first();

        if (! $stock || $stock->availableQuantity() < $quantity) {
            return false;
        }

        $stock->increment('reserved_quantity', $quantity);

        return true;
    }

    /**
     * Release previously reserved stock. Returns false if no stock record exists.
     */
    public function releaseStock(Product $product, Warehouse $warehouse, int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $stock = ProductWarehouseStock::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->first();

        if (! $stock) {
            return false;
        }

        $release = min($quantity, $stock->reserved_quantity);
        $stock->decrement('reserved_quantity', $release);

        return true;
    }

    /**
     * Synchronise the product-level stock quantity with the sum of all warehouse
     * stock quantities.
     */
    private function syncProductStock(Product $product): void
    {
        $total = ProductWarehouseStock::where('product_id', $product->id)->sum('quantity') ?? 0;
        $product->stock_quantity = (int) $total;
        $product->saveQuietly();
    }
}
