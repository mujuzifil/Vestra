<?php

namespace App\Http\Controllers\Api\V1\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AccountDashboardResource;
use App\Services\AccountDashboardService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly AccountDashboardService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->forUser($request->user());

        return $this->successResponse(new AccountDashboardResource($data));
    }
}
