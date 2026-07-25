<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use App\Services\RecommendationService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly RecommendationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $limit = max(1, min(12, $request->integer('limit', 6)));

        return $this->successResponse([
            'best_sellers' => ProductResource::collection($this->service->bestSellers($limit)),
            'new_arrivals' => ProductResource::collection($this->service->newArrivals($limit)),
            'trending' => ProductResource::collection($this->service->trending($limit)),
            'recently_viewed' => ProductResource::collection($this->service->recentlyViewed($request->user()?->id, $limit)),
        ]);
    }

    public function forProduct(Request $request, string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->where('status', 'active')->first();

        if (! $product) {
            return $this->errorResponse('Product not found.', 404);
        }

        $limit = max(1, min(12, $request->integer('limit', 4)));

        return $this->successResponse([
            'related' => ProductResource::collection($this->service->relatedProducts($product, $limit)),
            'frequently_bought_together' => ProductResource::collection($this->service->frequentlyBoughtTogether($product, $limit)),
        ]);
    }
}
