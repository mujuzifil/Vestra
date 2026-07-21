<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly OrderStatusService $orderStatusService,
    ) {}

    public function initiate(Order $order): array
    {
        $reference = 'VST-PAY-' . strtoupper(Str::random(12));

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'payment_method' => $order->payment_method,
            'provider' => 'flutterwave',
            'transaction_reference' => $reference,
            'amount' => $order->total_amount,
            'currency' => 'UGX',
            'status' => 'initiated',
        ]);

        $result = $this->gateway->initiate(
            (float) $order->total_amount,
            'UGX',
            $reference,
            [
                'order_id' => $order->id,
                'customer_email' => $order->user->email,
                'customer_name' => $order->user->name,
                'redirect_url' => config('app.frontend_url') . '/checkout/return',
            ]
        );

        if (! $result['success']) {
            $transaction->update(['status' => 'failed']);
            return $result;
        }

        $transaction->update(['status' => 'pending']);

        return [
            'success' => true,
            'payment_link' => $result['payment_link'],
            'transaction_reference' => $reference,
        ];
    }

    public function verifyAndProcess(string $reference): array
    {
        $transaction = PaymentTransaction::where('transaction_reference', $reference)->first();

        if (! $transaction) {
            return ['success' => false, 'message' => 'Transaction not found.'];
        }

        // Idempotency: already processed
        if ($transaction->status === 'success') {
            return ['success' => true, 'message' => 'Payment already confirmed.'];
        }

        $result = $this->gateway->verify($reference);

        if (! $result['success']) {
            $transaction->update([
                'status' => 'failed',
                'response_data' => $result,
            ]);
            return $result;
        }

        $transaction->update([
            'status' => 'success',
            'provider_reference' => $result['provider_reference'],
            'paid_at' => $result['paid_at'],
            'response_data' => $result,
        ]);

        // Update order
        $order = $transaction->order;
        $order->update([
            'payment_status' => PaymentStatus::PAID->value,
        ]);

        // Decrement stock if not already done (for digital payments)
        $this->decrementStock($order);

        // Update status to paid
        $this->orderStatusService->transition($order, OrderStatus::PAID, 'Payment confirmed via Flutterwave.');

        return ['success' => true, 'message' => 'Payment verified and order updated.'];
    }

    public function handleCallback(array $payload): array
    {
        $result = $this->gateway->handleCallback($payload);

        if (! $result['success'] || empty($payload['tx_ref'])) {
            return $result;
        }

        return $this->verifyAndProcess($payload['tx_ref']);
    }

    private function decrementStock(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->decrement('stock_quantity', $item->quantity);
                }
            }
        });
    }
}
