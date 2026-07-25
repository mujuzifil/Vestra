<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\CartService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WishlistController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request): JsonResponse
    {
        $items = Wishlist::where('user_id', $request->user()->id)
            ->with(['product.images', 'product.category'])
            ->latest()
            ->paginate($request->get('per_page', 20));

        return $this->successResponse([
            'items' => $items->map(fn (Wishlist $item) => [
                'id' => $item->id,
                'list_name' => $item->list_name,
                'notes' => $item->notes,
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
            'list_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $productId = $data['product_id'];
        $listName = $data['list_name'] ?? 'Default';

        if (Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->where('list_name', $listName)
            ->exists()) {
            throw ValidationException::withMessages([
                'product_id' => ['This product is already in your wishlist.'],
            ]);
        }

        $item = Wishlist::create([
            'user_id' => $request->user()->id,
            'product_id' => $productId,
            'list_name' => $listName,
            'notes' => $data['notes'] ?? null,
        ]);

        return $this->successResponse(
            [
                'id' => $item->id,
                'list_name' => $item->list_name,
                'notes' => $item->notes,
                'product' => new ProductResource($item->product()->with(['images', 'category'])->first()),
                'created_at' => $item->created_at->toDateTimeString(),
            ],
            'Product added to wishlist.',
            201
        );
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->delete();

        return $this->successResponse(null, 'Product removed from wishlist.');
    }

    public function merge(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.list_name' => 'nullable|string|max:100',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        $userId = $request->user()->id;
        $inserted = 0;

        foreach ($data['items'] as $item) {
            $productId = $item['product_id'];
            $listName = $item['list_name'] ?? 'Default';

            if (Wishlist::where('user_id', $userId)
                ->where('product_id', $productId)
                ->where('list_name', $listName)
                ->exists()) {
                continue;
            }

            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'list_name' => $listName,
                'notes' => $item['notes'] ?? null,
            ]);
            $inserted++;
        }

        return $this->successResponse(null, "{$inserted} item(s) merged into your wishlist.");
    }

    public function moveToCart(Request $request, int $productId): JsonResponse
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->first();

        if (! $wishlist) {
            return $this->errorResponse('Wishlist item not found.', 404);
        }

        $product = Product::findOrFail($productId);

        if ($product->stock_quantity <= 0) {
            return $this->errorResponse('Product is out of stock.', 422);
        }

        $this->cartService->addItem($request->user(), $product->id, 1);

        $wishlist->delete();

        return $this->successResponse(null, 'Product moved to cart.');
    }
}
