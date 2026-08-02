<?php

namespace App\Http\Controllers\Api\V1\Account;

use App\Events\Account\CustomerDocumentDownloaded;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CustomerDocumentResource;
use App\Models\CustomerDocument;
use App\Services\CustomerDocumentService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly CustomerDocumentService $service) {}

    public function index(Request $request): JsonResponse
    {
        $documents = $this->service->listForUser($request->user(), $request->integer('per_page', 15));
        $resource = CustomerDocumentResource::collection($documents)->response()->getData(true);

        return $this->successResponse([
            'data' => $resource['data'],
            'current_page' => $resource['meta']['current_page'],
            'last_page' => $resource['meta']['last_page'],
            'per_page' => $resource['meta']['per_page'],
            'total' => $resource['meta']['total'],
        ]);
    }

    public function download(Request $request, CustomerDocument $document)
    {
        $this->authorize('download', $document);
        if (! Storage::disk('public')->exists($document->file_path)) {
            return $this->errorResponse('File not found.', 404);
        }

        CustomerDocumentDownloaded::dispatch($request->user(), $document);

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}
