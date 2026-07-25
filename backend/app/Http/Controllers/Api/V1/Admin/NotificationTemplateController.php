<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreNotificationTemplateRequest;
use App\Http\Requests\Api\V1\Admin\UpdateNotificationTemplateRequest;
use App\Http\Resources\V1\NotificationTemplateResource;
use App\Models\NotificationTemplate;
use App\Services\NotificationTemplateService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        protected NotificationTemplateService $templateService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $templates = NotificationTemplate::query()
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->filled('active'), fn ($q) => $q->where('is_active', $request->boolean('active')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            NotificationTemplateResource::collection($templates)
        );
    }

    public function show(NotificationTemplate $template): JsonResponse
    {
        return $this->successResponse(
            new NotificationTemplateResource($template)
        );
    }

    public function store(StoreNotificationTemplateRequest $request): JsonResponse
    {
        $template = $this->templateService->upsert($request->validated());

        return $this->successResponse(
            new NotificationTemplateResource($template),
            'Template created successfully.',
            201
        );
    }

    public function update(UpdateNotificationTemplateRequest $request, NotificationTemplate $template): JsonResponse
    {
        $template = $this->templateService->upsert(array_merge(
            $request->validated(),
            ['event_key' => $template->event_key]
        ));

        return $this->successResponse(
            new NotificationTemplateResource($template),
            'Template updated successfully.'
        );
    }

    public function destroy(NotificationTemplate $template): JsonResponse
    {
        $template->delete();

        return $this->successResponse(null, 'Template deleted.');
    }
}
