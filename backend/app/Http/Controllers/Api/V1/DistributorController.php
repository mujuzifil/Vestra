<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDistributorRequest;
use App\Http\Resources\V1\DistributorResource;
use App\Models\DistributorRequest;
use App\Services\DistributorService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function applicationStatus(Request $request): JsonResponse
    {
        $application = DistributorRequest::where('email', $request->user()->email)
            ->latest()
            ->first();

        return $this->successResponse(
            $application ? [
                'id' => $application->id,
                'company_name' => $application->company_name,
                'contact_person' => $application->contact_person,
                'email' => $application->email,
                'phone' => $application->phone,
                'status' => $application->status->value,
                'created_at' => $application->created_at,
                'updated_at' => $application->updated_at,
            ] : null
        );
    }
}
