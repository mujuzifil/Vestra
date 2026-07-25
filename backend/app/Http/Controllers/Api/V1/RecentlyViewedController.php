<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductResource;
use App\Models\RecentlyViewedProduct;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecentlyViewedController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $items = RecentlyViewedProduct::where('user_id', $request->user()->id)
            ->with(['product.images', 'product.category'])
            ->orderByDesc('viewed_at')
            ->paginate($request->get('per_page', 20));

        return $this->successResponse([
            'items' => $items->map(fn (RecentlyViewedProduct $item) => [
                'id' => $item->id,
                'viewed_at' => $item->viewed_at->toDateTimeString(),
                'product' => new ProductResource($item->product),
            ]),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        RecentlyViewedProduct::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'product_id' => $data['product_id'],
            ],
            ['viewed_at' => now()]
        );

        return $this->successResponse(null, 'View recorded.', 201);
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        RecentlyViewedProduct::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->delete();

        return $this->successResponse(null, 'Product removed from recently viewed.');
    }

    public function clear(Request $request): JsonResponse
    {
        RecentlyViewedProduct::where('user_id', $request->user()->id)->delete();

        return $this->successResponse(null, 'Recently viewed history cleared.');
    }
}
