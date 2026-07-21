<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AddToCartRequest;
use App\Http\Requests\Api\V1\UpdateCartItemRequest;
use App\Http\Resources\V1\CartResource;
use App\Services\CartService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly CartService $service) {}

    public function index(Request $request): JsonResponse
    {
        $cart = $this->service->getCart($request->user());

        return $this->successResponse(
            new CartResource($cart)
        );
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->service->addItem(
            $request->user(),
            $request->validated('product_id'),
            $request->validated('quantity', 1)
        );

        return $this->successResponse(
            new CartResource($cart),
            'Item added to cart.',
            201
        );
    }

    public function update(UpdateCartItemRequest $request, int $item): JsonResponse
    {
        $cart = $this->service->updateItem(
            $request->user(),
            $item,
            $request->validated('quantity')
        );

        return $this->successResponse(
            new CartResource($cart),
            'Cart item updated.'
        );
    }

    public function destroy(Request $request, int $item): JsonResponse
    {
        $cart = $this->service->removeItem($request->user(), $item);

        return $this->successResponse(
            new CartResource($cart),
            'Item removed from cart.'
        );
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->service->clearCart($request->user());

        return $this->successResponse(
            new CartResource($cart),
            'Cart cleared.'
        );
    }

    public function merge(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        $cart = $this->service->mergeGuestCart($request->user(), $items);

        return $this->successResponse(
            new CartResource($cart),
            'Cart merged successfully.'
        );
    }
}
