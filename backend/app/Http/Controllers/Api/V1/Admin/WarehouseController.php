<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\WarehouseResource;
use App\Models\Warehouse;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $query = Warehouse::query()->withCount('stocks');

        if ($request->boolean('active_only')) {
            $query->active();
        }

        return $this->successResponse(
            WarehouseResource::collection($query->paginate($request->input('per_page', 15)))
        );
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        return $this->successResponse(
            new WarehouseResource($warehouse->load('stocks.product'))
        );
    }
}
