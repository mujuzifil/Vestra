<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InitiatePaymentRequest;
use App\Http\Requests\Api\V1\PaymentCallbackRequest;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly PaymentService $service) {}

    public function initiate(InitiatePaymentRequest $request): JsonResponse
    {
        $order = Order::find($request->validated('order_id'));

        if (! $order) {
            return $this->errorResponse('Order not found.', 404);
        }

        if ($order->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        $result = $this->service->initiate($order);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 400);
        }

        return $this->successResponse([
            'payment_link' => $result['payment_link'],
            'transaction_reference' => $result['transaction_reference'],
        ], 'Payment initiated. Please complete payment.');
    }

    public function verify(Request $request, string $reference): JsonResponse
    {
        $result = $this->service->verifyAndProcess($reference);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 400);
        }

        return $this->successResponse(null, $result['message']);
    }

    public function callback(PaymentCallbackRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $result = $this->service->handleCallback($payload);

        if (! $result['success']) {
            Log::warning('Payment callback processing failed.', [
                'tx_ref' => $payload['tx_ref'] ?? null,
                'status' => $payload['status'] ?? null,
                'ip' => $request->ip(),
                'message' => $result['message'] ?? 'Unknown failure',
            ]);

            return $this->errorResponse($result['message'], 400);
        }

        return $this->successResponse(null, $result['message']);
    }

    public function getTransaction(Request $request, string $reference): JsonResponse
    {
        $transaction = PaymentTransaction::where('transaction_reference', $reference)
            ->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id))
            ->first();

        if (! $transaction) {
            return $this->errorResponse('Transaction not found.', 404);
        }

        return $this->successResponse([
            'status' => $transaction->status,
            'amount' => $transaction->amount,
            'paid_at' => $transaction->paid_at,
            'order_id' => $transaction->order_id,
        ]);
    }
}
