<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::query()
            ->with(['supplier', 'warehouse'])
            ->withSum('items', 'total_price');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->boolean('open_only')) {
            $query->open();
        }

        return $this->successResponse(
            PurchaseOrderResource::collection($query->paginate($request->input('per_page', 15)))->response()->getData(true)
        );
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        return $this->successResponse(
            new PurchaseOrderResource($purchaseOrder->load(['supplier', 'warehouse', 'items.product']))
        );
    }

    public function statusCounts(): JsonResponse
    {
        return $this->successResponse([
            'draft' => PurchaseOrder::where('status', PurchaseOrderStatus::DRAFT->value)->count(),
            'ordered' => PurchaseOrder::where('status', PurchaseOrderStatus::ORDERED->value)->count(),
            'partial' => PurchaseOrder::where('status', PurchaseOrderStatus::PARTIAL->value)->count(),
            'received' => PurchaseOrder::where('status', PurchaseOrderStatus::RECEIVED->value)->count(),
            'cancelled' => PurchaseOrder::where('status', PurchaseOrderStatus::CANCELLED->value)->count(),
        ]);
    }
}
