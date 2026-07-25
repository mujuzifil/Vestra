<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Distributor\InvoiceResource;
use App\Models\Order;
use App\Services\InvoicePdfService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly InvoicePdfService $service) {}

    public function index(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;

        $invoices = $distributor->orders()
            ->whereNotNull('invoice_number')
            ->orderByDesc('created_at')
            ->get();

        return $this->successResponse(
            InvoiceResource::collection($invoices)
        );
    }

    public function show(Request $request, Order $invoice): JsonResponse|StreamedResponse
    {
        $distributor = $request->user()->distributor;

        if ($invoice->distributor_id !== $distributor->id) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        if ($request->boolean('download')) {
            $path = $this->service->save($invoice);

            return response()->streamDownload(function () use ($path) {
                echo file_get_contents($path);
            }, "{$invoice->invoice_number}.pdf", [
                'Content-Type' => 'application/pdf',
            ]);
        }

        return $this->successResponse(
            new InvoiceResource($invoice->load('items'))
        );
    }
}
