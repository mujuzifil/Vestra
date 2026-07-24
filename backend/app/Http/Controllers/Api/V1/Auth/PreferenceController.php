<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdatePreferenceRequest;
use App\Http\Resources\V1\PreferenceResource;
use App\Models\CustomerPreference;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    use RespondsWithJson;

    public function show(Request $request): JsonResponse
    {
        $preference = $this->getOrCreatePreference($request->user());

        return $this->successResponse(
            new PreferenceResource($preference)
        );
    }

    public function update(UpdatePreferenceRequest $request): JsonResponse
    {
        $user = $request->user();
        $preference = $this->getOrCreatePreference($user);

        $validated = $request->validated();

        if (isset($validated['notification_preferences'])) {
            $preference->notification_preferences = $validated['notification_preferences'];
        }

        if (isset($validated['account_preferences'])) {
            $preference->account_preferences = $validated['account_preferences'];
        }

        $preference->save();

        AuditService::log(
            $user,
            'preferences_updated',
            $preference,
            ['source' => 'api'],
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new PreferenceResource($preference->fresh()),
            'Preferences updated successfully.'
        );
    }

    private function getOrCreatePreference($user): CustomerPreference
    {
        return CustomerPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'notification_preferences' => [],
                'account_preferences' => [],
            ]
        );
    }
}
