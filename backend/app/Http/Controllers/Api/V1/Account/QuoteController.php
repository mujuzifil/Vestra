<?php

namespace App\Http\Controllers\Api\V1\Account;

use App\Events\Account\QuoteViewed;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CustomerQuoteResource;
use App\Services\CustomerQuoteService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuoteController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly CustomerQuoteService $service) {}

    public function index(Request $request): JsonResponse
    {
        $quotes = $this->service->listForUser($request->user(), $request->integer('per_page', 15));
        $resource = CustomerQuoteResource::collection($quotes)->response()->getData(true);

        return $this->successResponse([
            'data' => $resource['data'],
            'current_page' => $resource['meta']['current_page'],
            'last_page' => $resource['meta']['last_page'],
            'per_page' => $resource['meta']['per_page'],
            'total' => $resource['meta']['total'],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $quote = $this->service->findForUser($request->user(), $id);
        if (! $quote) {
            return $this->errorResponse('Quote not found.', 404);
        }
        $this->authorize('viewAsCustomer', $quote);

        QuoteViewed::dispatch($request->user(), $quote);

        return $this->successResponse(new CustomerQuoteResource($quote));
    }

    public function downloadAttachment(Request $request, int $id, int $index)
    {
        $quote = $this->service->findForUser($request->user(), $id);
        if (! $quote) {
            return $this->errorResponse('Quote not found.', 404);
        }
        $this->authorize('downloadAsCustomer', $quote);

        $attachments = $quote->attachments ?? [];
        $path = $attachments[$index] ?? null;
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return $this->errorResponse('Attachment not found.', 404);
        }

        return Storage::disk('public')->download($path);
    }
}
