<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Services\OrderService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly OrderService $service) {}

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
}
