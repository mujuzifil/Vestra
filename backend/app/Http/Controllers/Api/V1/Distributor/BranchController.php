<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Distributor\StoreBranchRequest;
use App\Http\Requests\Api\V1\Distributor\UpdateBranchRequest;
use App\Http\Resources\V1\Distributor\BranchResource;
use App\Models\DistributorBranch;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;
        $branches = $distributor->branches()->orderBy('name')->get();

        return $this->successResponse(
            BranchResource::collection($branches)
        );
    }

    public function store(StoreBranchRequest $request): JsonResponse
    {
        $distributor = $request->user()->distributor;

        $branch = $distributor->branches()->create($request->validated());

        if ($request->boolean('is_default')) {
            $branch->setAsDefault();
        }

        AuditService::log(
            $request->user(),
            'distributor_branch_created',
            $branch,
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new BranchResource($branch),
            'Branch created successfully.',
            201
        );
    }

    public function show(Request $request, DistributorBranch $branch): JsonResponse
    {
        $this->authorize('view', $branch);

        return $this->successResponse(
            new BranchResource($branch)
        );
    }

    public function update(UpdateBranchRequest $request, DistributorBranch $branch): JsonResponse
    {
        $this->authorize('update', $branch);

        $branch->update($request->validated());

        if ($request->has('is_default') && $request->boolean('is_default')) {
            $branch->setAsDefault();
        }

        AuditService::log(
            $request->user(),
            'distributor_branch_updated',
            $branch,
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new BranchResource($branch->fresh()),
            'Branch updated successfully.'
        );
    }

    public function destroy(Request $request, DistributorBranch $branch): JsonResponse
    {
        $this->authorize('delete', $branch);

        $branch->delete();

        AuditService::log(
            $request->user(),
            'distributor_branch_deleted',
            $branch,
            null,
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            null,
            'Branch deleted successfully.'
        );
    }
}
