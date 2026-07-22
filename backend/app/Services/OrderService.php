<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Repositories\OrderRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $repository,
        private readonly PaymentService $paymentService,
    ) {}

    public function createOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $cart = $user->cart?->load('items.product');

            if (! $cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Your cart is empty.'],
                ]);
            }

            // Validate stock and calculate totals
            $subtotal = 0;
            $orderItems = [];
            $productIds = [];

            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;

                if ($product->status !== ProductStatus::ACTIVE) {
                    throw ValidationException::withMessages([
                        'stock' => ["{$product->name} is currently unavailable."],
                    ]);
                }

                if ($product->stock_quantity < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => ["Not enough stock for {$product->name}. Only {$product->stock_quantity} left."],
                    ]);
                }

                $lineTotal = $cartItem->quantity * $product->price;
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $product->price,
                    'quantity' => $cartItem->quantity,
                    'line_total' => $lineTotal,
                ];

                $productIds[] = $product->id;
            }

            // Server-side calculation prevents client-side price manipulation.
            $shippingCost = $this->shippingCost($subtotal);
            $taxAmount = $this->taxAmount($subtotal);
            $totalAmount = round($subtotal + $shippingCost + $taxAmount, 2);
            $paymentMethod = $data['payment_method'];
            $isCod = $paymentMethod === PaymentMethod::COD->value;

            $order = $this->repository->create([
                'user_id' => $user->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'status' => OrderStatus::PENDING->value,
                'payment_method' => $paymentMethod,
                'payment_status' => PaymentStatus::PENDING->value,
                'stock_decremented' => $isCod,
                'shipping_address' => $data['shipping_address'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
            ]);

            $order->items()->createMany($orderItems);

            // Lock products and decrement stock immediately for COD.
            if ($isCod) {
                $this->decrementStockForOrder($order, $productIds);
            }

            // Clear cart
            $cart->items()->delete();

            $order->load('items');

            // Initiate digital payment
            if (! $isCod) {
                $paymentResult = $this->paymentService->initiate($order);
                if ($paymentResult['success']) {
                    $order->setAttribute('payment_url', $paymentResult['payment_link']);
                }
            }

            return $order;
        });
    }

    public function getUserOrders(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->orders()->with('items')->orderBy('created_at', 'desc')->get();
    }

    public function getOrder(User $user, int $orderId): ?Order
    {
        return $user->orders()->with('items')->find($orderId);
    }

    /**
     * Determine whether an order can be cancelled by its owner.
     */
    public function canCustomerCancel(Order $order): bool
    {
        return in_array($order->status, [
            OrderStatus::PENDING->value,
            OrderStatus::PAID->value,
            OrderStatus::PROCESSING->value,
        ], true);
    }

    /**
     * Decrement stock for each item in the order using row locks.
     *
     * @param  array<int, int>  $productIds
     */
    private function decrementStockForOrder(Order $order, array $productIds): void
    {
        if ($productIds === []) {
            return;
        }

        // Ensure the relationship is loaded before we acquire locks.
        $order->load('items');

        // Lock products in a consistent order to reduce deadlock risk.
        sort($productIds);
        $lockedProducts = Product::lockForUpdate()->whereIn('id', array_unique($productIds))
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            $product = $lockedProducts->get($item->product_id);

            if (! $product) {
                continue;
            }

            $product->decrement('stock_quantity', $item->quantity);
        }
    }

    private function shippingCost(float $subtotal): float
    {
        $setting = Setting::where('key', 'shipping_cost')->first();
        $configured = $setting?->typedValue();

        return is_numeric($configured) ? (float) $configured : 0.0;
    }

    private function taxAmount(float $subtotal): float
    {
        $setting = Setting::where('key', 'tax_rate')->first();
        $rate = is_numeric($setting?->typedValue()) ? (float) $setting->typedValue() : 0.18;

        return round($subtotal * $rate, 2);
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'VST';
        $date = Carbon::now()->format('Ymd');
        $sequence = Order::whereDate('created_at', Carbon::today())->count() + 1;
        $sequence = str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$sequence}";
    }
}
