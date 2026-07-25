<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use App\Models\SavedItem;
use App\Services\CartService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SavedItemController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request): JsonResponse
    {
        $items = SavedItem::where('user_id', $request->user()->id)
            ->with(['product.images', 'product.category'])
            ->latest()
            ->paginate($request->get('per_page', 20));

        return $this->successResponse([
            'items' => $items->map(fn (SavedItem $item) => [
                'id' => $item->id,
                'product' => new ProductResource($item->product),
                'created_at' => $item->created_at->toDateTimeString(),
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

        $productId = $data['product_id'];

        if (SavedItem::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->exists()) {
            throw ValidationException::withMessages([
                'product_id' => ['This product is already saved for later.'],
            ]);
        }

        $item = SavedItem::create([
            'user_id' => $request->user()->id,
            'product_id' => $productId,
        ]);

        return $this->successResponse(
            [
                'id' => $item->id,
                'product' => new ProductResource($item->product()->with(['images', 'category'])->first()),
                'created_at' => $item->created_at->toDateTimeString(),
            ],
            'Product saved for later.',
            201
        );
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        SavedItem::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->delete();

        return $this->successResponse(null, 'Product removed from saved items.');
    }

    public function merge(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
        ]);

        $userId = $request->user()->id;
        $inserted = 0;

        foreach ($data['items'] as $item) {
            $productId = $item['product_id'];

            if (SavedItem::where('user_id', $userId)->where('product_id', $productId)->exists()) {
                continue;
            }

            SavedItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);
            $inserted++;
        }

        return $this->successResponse(null, "{$inserted} item(s) merged into saved for later.");
    }

    public function moveToCart(Request $request, int $productId): JsonResponse
    {
        $saved = SavedItem::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->first();

        if (! $saved) {
            return $this->errorResponse('Saved item not found.', 404);
        }

        $product = Product::findOrFail($productId);

        if ($product->stock_quantity <= 0) {
            return $this->errorResponse('Product is out of stock.', 422);
        }

        $this->cartService->addItem($request->user(), $product->id, 1);

        $saved->delete();

        return $this->successResponse(null, 'Product moved to cart.');
    }
}
