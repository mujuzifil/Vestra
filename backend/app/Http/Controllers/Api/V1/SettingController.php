<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SettingResource;
use App\Services\SettingService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SettingService $service) {}

    public function index(): JsonResponse
    {
        $settings = $this->service->publicList();

        return $this->successResponse(SettingResource::collection($settings));
    }
}
