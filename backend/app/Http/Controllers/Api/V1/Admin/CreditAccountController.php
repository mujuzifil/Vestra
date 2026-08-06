<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CreditAccountResource;
use App\Models\CreditAccount;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreditAccountController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $query = CreditAccount::query()->with('distributor');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        return $this->successResponse(
            CreditAccountResource::collection($query->paginate($request->input('per_page', 15)))->response()->getData(true)
        );
    }

    public function show(CreditAccount $creditAccount): JsonResponse
    {
        return $this->successResponse(
            new CreditAccountResource($creditAccount->load(['distributor', 'transactions']))
        );
    }

    public function summary(): JsonResponse
    {
        return $this->successResponse([
            'total_credit_limit' => (float) (CreditAccount::sum('limit') ?? 0),
            'total_outstanding' => (float) (CreditAccount::sum('balance') ?? 0),
            'total_available' => (float) max(0, (CreditAccount::sum('limit') ?? 0) - (CreditAccount::sum('balance') ?? 0) - (CreditAccount::sum('authorized_amount') ?? 0)),
            'account_count' => CreditAccount::count(),
        ]);
    }
}
