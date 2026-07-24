<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Distributor\NotificationResource;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;

        // In-app notifications are deferred to a simple list derived from recent
        // distributor-related activity. Email/SMS delivery is intentionally not
        // implemented in this iteration.
        $notifications = $distributor->orders()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($order) => [
                'id' => 'order-' . $order->id,
                'type' => 'order',
                'title' => 'Order ' . $order->invoice_number,
                'message' => 'Your order status is ' . $order->status . '.',
                'data' => [
                    'order_id' => $order->id,
                    'invoice_number' => $order->invoice_number,
                    'status' => $order->status,
                ],
                'read_at' => null,
                'created_at' => $order->created_at->toISOString(),
            ])
            ->merge(
                $distributor->quotations()
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(fn ($quotation) => [
                        'id' => 'quote-' . $quotation->id,
                        'type' => 'quotation',
                        'title' => 'Quotation ' . $quotation->reference_number,
                        'message' => 'Your quotation status is ' . $quotation->status->value . '.',
                        'data' => [
                            'quotation_id' => $quotation->id,
                            'reference_number' => $quotation->reference_number,
                            'status' => $quotation->status->value,
                        ],
                        'read_at' => null,
                        'created_at' => $quotation->created_at->toISOString(),
                    ])
            )
            ->sortByDesc('created_at')
            ->take(20)
            ->values();

        return $this->successResponse(
            NotificationResource::collection($notifications)
        );
    }
}
