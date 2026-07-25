<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Distributor\UpdateProfileRequest;
use App\Http\Requests\Api\V1\Distributor\UploadLogoRequest;
use App\Http\Resources\V1\Distributor\DistributorProfileResource;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use RespondsWithJson;

    public function show(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;

        $this->authorize('view', $distributor);

        return $this->successResponse(
            new DistributorProfileResource($distributor)
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $distributor = $request->user()->distributor;

        $this->authorize('update', $distributor);

        $distributor->update($request->validated());

        AuditService::log(
            $request->user(),
            'distributor_profile_updated',
            $distributor,
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new DistributorProfileResource($distributor->fresh()),
            'Profile updated successfully.'
        );
    }

    public function uploadLogo(UploadLogoRequest $request): JsonResponse
    {
        $distributor = $request->user()->distributor;

        $this->authorize('update', $distributor);

        if ($distributor->logo_path) {
            Storage::disk('public')->delete($distributor->logo_path);
        }

        $path = $request->file('logo')->store('distributor-logos', 'public');
        $distributor->update(['logo_path' => "storage/{$path}"]);

        AuditService::log(
            $request->user(),
            'distributor_logo_uploaded',
            $distributor,
            ['logo_path' => $distributor->logo_path],
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new DistributorProfileResource($distributor->fresh()),
            'Logo uploaded successfully.'
        );
    }

    public function removeLogo(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;

        $this->authorize('update', $distributor);

        if ($distributor->logo_path) {
            Storage::disk('public')->delete(str_replace('storage/', '', $distributor->logo_path));
            $distributor->update(['logo_path' => null]);
        }

        AuditService::log(
            $request->user(),
            'distributor_logo_removed',
            $distributor,
            null,
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new DistributorProfileResource($distributor->fresh()),
            'Logo removed successfully.'
        );
    }
}
