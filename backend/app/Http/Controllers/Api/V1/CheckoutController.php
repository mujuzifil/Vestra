<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckoutRequest;
use App\Http\Resources\V1\OrderResource;
use App\Services\OrderService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly OrderService $service) {}

    public function store(CheckoutRequest $request): JsonResponse
    {
        $order = $this->service->createOrder($request->user(), $request->validated());

        return $this->successResponse(
            new OrderResource($order),
            'Order placed successfully.',
            201
        );
    }
}
