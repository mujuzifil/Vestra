<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SupplierResource;
use App\Models\Supplier;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $query = Supplier::query()->withCount('purchaseOrders');

        if ($request->boolean('active_only')) {
            $query->active();
        }

        return $this->successResponse(
            SupplierResource::collection($query->paginate($request->input('per_page', 15)))->response()->getData(true)
        );
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return $this->successResponse(
            new SupplierResource($supplier->load('purchaseOrders'))
        );
    }
}
