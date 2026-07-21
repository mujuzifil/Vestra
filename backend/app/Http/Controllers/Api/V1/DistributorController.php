<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDistributorRequest;
use App\Http\Resources\V1\DistributorResource;
use App\Services\DistributorService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;

class DistributorController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly DistributorService $service) {}

    public function store(StoreDistributorRequest $request): JsonResponse
    {
        $request = $this->service->submit($request->validated());

        return $this->successResponse(
            new DistributorResource($request),
            'Your distributor request has been submitted successfully.',
            201
        );
    }
}
