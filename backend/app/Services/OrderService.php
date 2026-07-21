<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
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

            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;

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
            }

            $shippingCost = $data['shipping_cost'] ?? 0;
            $taxAmount = $data['tax_amount'] ?? 0;
            $totalAmount = $subtotal + $shippingCost + $taxAmount;
            $paymentMethod = $data['payment_method'];
            $isCod = $paymentMethod === PaymentMethod::COD->value;

            $order = new Order();
            $order->forceFill([
                'user_id' => $user->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'status' => OrderStatus::PENDING->value,
                'payment_method' => $paymentMethod,
                'payment_status' => PaymentStatus::PENDING->value,
                'shipping_address' => $data['shipping_address'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
            ])->save();

            $order->items()->createMany($orderItems);

            // Decrement stock immediately for COD; digital payments defer until paid
            if ($isCod) {
                foreach ($cart->items as $cartItem) {
                    $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
                }
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

    private function generateInvoiceNumber(): string
    {
        $prefix = 'VST';
        $date = Carbon::now()->format('Ymd');
        $sequence = Order::whereDate('created_at', Carbon::today())->count() + 1;
        $sequence = str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$sequence}";
    }
}
