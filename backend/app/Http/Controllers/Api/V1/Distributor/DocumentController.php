<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Distributor\StoreDocumentRequest;
use App\Http\Resources\V1\Distributor\DocumentResource;
use App\Models\DistributorDocument;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;
        $documents = $distributor->documents()->latest()->get();

        return $this->successResponse(
            DocumentResource::collection($documents)
        );
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $this->authorize('create', DistributorDocument::class);

        $distributor = $request->user()->distributor;

        $path = $request->file('file')->store('distributor-documents', 'public');

        $document = $distributor->documents()->create([
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            'file_path' => "storage/{$path}",
            'uploaded_by' => $request->user()->id,
            'version' => $request->input('version', '1.0'),
        ]);

        AuditService::log(
            $request->user(),
            'distributor_document_uploaded',
            $document,
            ['title' => $document->title, 'type' => $document->type],
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new DocumentResource($document),
            'Document uploaded successfully.',
            201
        );
    }

    public function destroy(Request $request, DistributorDocument $document): JsonResponse
    {
        $this->authorize('delete', $document);

        Storage::disk('public')->delete(str_replace('storage/', '', $document->file_path));
        $document->delete();

        AuditService::log(
            $request->user(),
            'distributor_document_deleted',
            $document,
            null,
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            null,
            'Document deleted successfully.'
        );
    }
}
