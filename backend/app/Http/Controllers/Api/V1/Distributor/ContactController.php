<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Distributor\StoreContactRequest;
use App\Http\Requests\Api\V1\Distributor\UpdateContactRequest;
use App\Http\Resources\V1\Distributor\ContactResource;
use App\Models\DistributorContact;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;
        $contacts = $distributor->contacts()->orderBy('name')->get();

        return $this->successResponse(
            ContactResource::collection($contacts)
        );
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $distributor = $request->user()->distributor;

        $contact = $distributor->contacts()->create($request->validated());

        if ($request->boolean('is_primary')) {
            $contact->setAsPrimary();
        }

        AuditService::log(
            $request->user(),
            'distributor_contact_created',
            $contact,
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new ContactResource($contact),
            'Contact created successfully.',
            201
        );
    }

    public function show(Request $request, DistributorContact $contact): JsonResponse
    {
        $this->authorize('view', $contact);

        return $this->successResponse(
            new ContactResource($contact)
        );
    }

    public function update(UpdateContactRequest $request, DistributorContact $contact): JsonResponse
    {
        $this->authorize('update', $contact);

        $contact->update($request->validated());

        if ($request->has('is_primary') && $request->boolean('is_primary')) {
            $contact->setAsPrimary();
        }

        AuditService::log(
            $request->user(),
            'distributor_contact_updated',
            $contact,
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new ContactResource($contact->fresh()),
            'Contact updated successfully.'
        );
    }

    public function destroy(Request $request, DistributorContact $contact): JsonResponse
    {
        $this->authorize('delete', $contact);

        $contact->delete();

        AuditService::log(
            $request->user(),
            'distributor_contact_deleted',
            $contact,
            null,
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            null,
            'Contact deleted successfully.'
        );
    }
}
