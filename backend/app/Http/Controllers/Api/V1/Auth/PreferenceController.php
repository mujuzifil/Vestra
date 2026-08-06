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

        return $this->preferencesResponse(
            $this->formatPreferencePayload($preference)
        );
    }

    public function update(UpdatePreferenceRequest $request): JsonResponse
    {
        $user = $request->user();
        $preference = $this->getOrCreatePreference($user);

        $validated = $request->validated();

        if (isset($validated['notification_preferences']['email'])) {
            $validated['notification_preferences']['email_notifications'] = $validated['notification_preferences']['email'];
            unset($validated['notification_preferences']['email']);
        }

        if (isset($validated['notification_preferences'])) {
            $preference->notification_preferences = array_merge(
                $preference->notification_preferences ?? [],
                $validated['notification_preferences']
            );
        }

        if (isset($validated['account_preferences'])) {
            $preference->account_preferences = $validated['account_preferences'];
        }

        if (isset($validated['system_alerts'])) {
            $preference->system_alerts = $validated['system_alerts'];
        }

        if (isset($validated['emergency_alerts'])) {
            $preference->emergency_alerts = $validated['emergency_alerts'];
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

        return $this->preferencesResponse(
            $this->formatPreferencePayload($preference->fresh()),
            'Preferences updated successfully.'
        );
    }

    private function preferencesResponse(array $payload, string $message = 'Request completed successfully.'): JsonResponse
    {
        foreach (['notification_preferences', 'account_preferences'] as $key) {
            if ($payload[$key] === null || $payload[$key] === []) {
                $payload[$key] = new \stdClass();
            }
        }

        return response()->json([
            'success' => true,
            'data' => $payload,
            'message' => $message,
        ], $message === 'Request completed successfully.' ? 200 : 200);
    }

    private function formatPreferencePayload(CustomerPreference $preference): array
    {
        return [
            'notification_preferences' => empty($preference->notification_preferences)
                ? json_decode('{}')
                : $preference->notification_preferences,
            'account_preferences' => empty($preference->account_preferences)
                ? json_decode('{}')
                : $preference->account_preferences,
            'system_alerts' => $preference->system_alerts,
            'emergency_alerts' => $preference->emergency_alerts,
            'updated_at' => $preference->updated_at,
        ];
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
