<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Distributor\StorePaymentUploadRequest;
use App\Http\Resources\V1\Distributor\PaymentUploadResource;
use App\Models\PaymentUpload;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentUploadController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;
        $uploads = $distributor->paymentUploads()->latest()->get();

        return $this->successResponse(
            PaymentUploadResource::collection($uploads)
        );
    }

    public function store(StorePaymentUploadRequest $request): JsonResponse
    {
        $this->authorize('create', PaymentUpload::class);

        $distributor = $request->user()->distributor;

        $path = $request->file('file')->store('payment-uploads', 'public');

        $upload = $distributor->paymentUploads()->create([
            'amount' => $request->input('amount'),
            'currency' => 'UGX',
            'reference_number' => $request->input('reference_number'),
            'file_path' => "storage/{$path}",
            'notes' => $request->input('notes'),
            'status' => 'uploaded',
        ]);

        AuditService::log(
            $request->user(),
            'distributor_payment_uploaded',
            $upload,
            [
                'amount' => $upload->amount,
                'reference_number' => $upload->reference_number,
            ],
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new PaymentUploadResource($upload),
            'Payment proof uploaded successfully.',
            201
        );
    }
}
