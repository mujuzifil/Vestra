<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AutomatedWorkflowResource;
use App\Models\AutomatedWorkflow;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomatedWorkflowController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $query = AutomatedWorkflow::query();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('event')) {
            $query->where('event', $request->input('event'));
        }

        return $this->successResponse(
            AutomatedWorkflowResource::collection($query->paginate($request->input('per_page', 15)))
        );
    }

    public function show(AutomatedWorkflow $automatedWorkflow): JsonResponse
    {
        return $this->successResponse(
            new AutomatedWorkflowResource($automatedWorkflow)
        );
    }

    public function events(): JsonResponse
    {
        return $this->successResponse([
            'order.created',
            'order.paid',
            'order.shipped',
            'order.delivered',
            'distributor.approved',
            'stock.low',
            'payment.received',
        ]);
    }
}
