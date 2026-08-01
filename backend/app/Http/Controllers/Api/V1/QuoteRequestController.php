<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreQuoteRequestRequest;
use App\Http\Resources\V1\QuoteRequestResource;
use App\Services\QuoteRequestService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;

class QuoteRequestController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly QuoteRequestService $service) {}

    public function store(StoreQuoteRequestRequest $request): JsonResponse
    {
        $quote = $this->service->submit($request->validated());

        return $this->successResponse(
            new QuoteRequestResource($quote),
            'Thank you. Your quotation request has been received.',
            201
        );
    }
}
