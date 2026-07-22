<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Services\OrderService;
use App\Services\OrderStatusService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly OrderService $service,
        private readonly OrderStatusService $statusService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->service->getUserOrders($request->user());

        return $this->successResponse(
            OrderResource::collection($orders)
        );
    }

    public function show(Request $request, int $order): JsonResponse
    {
        $orderModel = $this->service->getOrder($request->user(), $order);

        if (! $orderModel) {
            return $this->errorResponse('Order not found.', 404);
        }

        return $this->successResponse(
            new OrderResource($orderModel)
        );
    }

    public function cancel(Request $request, int $order): JsonResponse
    {
        $orderModel = $this->service->getOrder($request->user(), $order);

        if (! $orderModel) {
            return $this->errorResponse('Order not found.', 404);
        }

        if (! $this->service->canCustomerCancel($orderModel)) {
            return $this->errorResponse('This order cannot be cancelled.', 422);
        }

        $success = $this->statusService->transition(
            $orderModel,
            OrderStatus::CANCELLED,
            'Cancelled by customer.',
            $request->user()->id
        );

        if (! $success) {
            return $this->errorResponse('Unable to cancel order.', 422);
        }

        return $this->successResponse(
            new OrderResource($orderModel->fresh()->load('items')),
            'Order cancelled successfully.'
        );
    }
}
