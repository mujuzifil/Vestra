<?php

namespace App\Http\Controllers\Api\V1\Account;

use App\Events\Account\CompanyProfileUpdated;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CompanyProfileResource;
use App\Services\CompanyProfileService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly CompanyProfileService $service) {}

    public function show(Request $request): JsonResponse
    {
        $profile = $this->service->getOrCreateForUser($request->user());
        $this->authorize('view', $profile);

        return $this->successResponse(new CompanyProfileResource($profile));
    }

    public function update(Request $request): JsonResponse
    {
        $profile = $this->service->getOrCreateForUser($request->user());
        $this->authorize('update', $profile);

        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:255'],
            'tax_identification' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'primary_contact_name' => ['nullable', 'string', 'max:255'],
            'primary_contact_phone' => ['nullable', 'string', 'max:50'],
            'primary_contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        $profile = $this->service->updateForUser($request->user(), $data);

        CompanyProfileUpdated::dispatch($request->user(), $profile);

        return $this->successResponse(new CompanyProfileResource($profile), 'Company profile updated.');
    }
}
