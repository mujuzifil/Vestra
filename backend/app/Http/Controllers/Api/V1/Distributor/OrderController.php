<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Distributor\OrderResource;
use App\Models\Order;
use App\Services\DistributorOrderService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly DistributorOrderService $service) {}

    public function index(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;
        $orders = $this->service->getDistributorOrders($distributor);

        return $this->successResponse(
            OrderResource::collection($orders)
        );
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $distributor = $request->user()->distributor;

        $orderModel = $this->service->getDistributorOrder($distributor, $order->id);

        if (! $orderModel) {
            return $this->errorResponse('Order not found.', 404);
        }

        $this->authorize('view', $orderModel);

        return $this->successResponse(
            new OrderResource($orderModel)
        );
    }
}
