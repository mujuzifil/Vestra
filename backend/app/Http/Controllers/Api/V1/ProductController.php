<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductResource;
use App\Services\ProductService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ProductService $service) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 12);
        $filters = [
            'category' => $request->input('category'),
            'search' => $request->input('search'),
            'featured' => $request->boolean('featured'),
            'sort' => $request->input('sort'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
        ];
        $products = $this->service->listActive(max(1, min($perPage, 100)), $filters);

        return $this->successResponse(ProductResource::collection($products));
    }

    public function show(string $slug): JsonResponse
    {
        $product = $this->service->findActiveBySlug($slug);

        if (! $product) {
            return $this->errorResponse('Product not found.', 404);
        }



        return $this->successResponse(new ProductResource($product));
    }
}
