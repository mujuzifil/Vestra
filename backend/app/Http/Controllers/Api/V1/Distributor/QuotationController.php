<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Distributor\StoreQuotationRequest;
use App\Http\Requests\Api\V1\Distributor\UpdateQuotationRequest;
use App\Http\Resources\V1\Distributor\QuotationResource;
use App\Models\QuotationRequest;
use App\Services\AuditService;
use App\Services\QuotationService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly QuotationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;
        $quotations = $distributor->quotations()
            ->with('items')
            ->orderByDesc('created_at')
            ->get();

        return $this->successResponse(
            QuotationResource::collection($quotations)
        );
    }

    public function store(StoreQuotationRequest $request): JsonResponse
    {
        $this->authorize('create', QuotationRequest::class);

        $distributor = $request->user()->distributor;
        $quotation = $this->service->createDraft($distributor, $request->validated());

        AuditService::log(
            $request->user(),
            'distributor_quotation_created',
            $quotation,
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new QuotationResource($quotation),
            'Quotation created successfully.',
            201
        );
    }

    public function show(Request $request, QuotationRequest $quote): JsonResponse
    {
        $this->authorize('view', $quote);

        return $this->successResponse(
            new QuotationResource($quote->load('items'))
        );
    }

    public function update(UpdateQuotationRequest $request, QuotationRequest $quote): JsonResponse
    {
        $this->authorize('update', $quote);

        $quotation = $this->service->updateDraft($quote, $request->validated());

        AuditService::log(
            $request->user(),
            'distributor_quotation_updated',
            $quotation,
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new QuotationResource($quotation),
            'Quotation updated successfully.'
        );
    }

    public function destroy(Request $request, QuotationRequest $quote): JsonResponse
    {
        $this->authorize('delete', $quote);

        $this->service->deleteDraft($quote);

        AuditService::log(
            $request->user(),
            'distributor_quotation_deleted',
            $quote,
            null,
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            null,
            'Quotation deleted successfully.'
        );
    }

    public function submit(Request $request, QuotationRequest $quote): JsonResponse
    {
        $this->authorize('update', $quote);

        $quotation = $this->service->submit($quote);

        AuditService::log(
            $request->user(),
            'distributor_quotation_submitted',
            $quotation,
            null,
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new QuotationResource($quotation),
            'Quotation submitted successfully.'
        );
    }

    public function accept(Request $request, QuotationRequest $quote): JsonResponse
    {
        $this->authorize('accept', $quote);

        $quotation = $this->service->accept($quote);

        AuditService::log(
            $request->user(),
            'distributor_quotation_accepted',
            $quotation,
            null,
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new QuotationResource($quotation),
            'Quotation accepted successfully.'
        );
    }
}
