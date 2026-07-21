<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryResource;
use App\Services\CategoryService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly CategoryService $service) {}

    public function index(Request $request): JsonResponse
    {
        $categories = $this->service->listActive();

        return $this->successResponse(CategoryResource::collection($categories));
    }
}
