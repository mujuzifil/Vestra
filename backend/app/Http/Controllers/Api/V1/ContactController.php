<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Http\Resources\V1\ContactResource;
use App\Services\ContactService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly ContactService $service) {}

    public function store(StoreContactRequest $request): JsonResponse
    {
        $message = $this->service->submit($request->validated());

        return $this->successResponse(
            new ContactResource($message),
            'Thank you for contacting us. We will get back to you soon.',
            201
        );
    }
}
