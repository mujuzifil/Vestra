<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Distributor\ProductResource;
use App\Models\Product;
use App\Services\DistributorPriceService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly DistributorPriceService $priceService) {}

    public function index(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;
        $perPage = $request->integer('per_page', 12);
        $perPage = max(1, min($perPage, 100));

        $query = Product::with(['category', 'images', 'distributorPriceTiers'])
            ->active();

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate($perPage);

        $products->getCollection()->transform(function (Product $product) use ($distributor) {
            $product->wholesale_price = $this->priceService->resolve($product, 1, $distributor);

            return $product;
        });

        return $this->successResponse(
            ProductResource::collection($products)
        );
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $distributor = $request->user()->distributor;

        $product = Product::with(['category', 'images', 'distributorPriceTiers'])
            ->active()
            ->where('slug', $slug)
            ->first();

        if (! $product) {
            return $this->errorResponse('Product not found.', 404);
        }

        $product->wholesale_price = $this->priceService->resolve($product, 1, $distributor);

        return $this->successResponse(
            new ProductResource($product)
        );
    }
}
