<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notification_preferences' => ['sometimes', 'array'],
            'notification_preferences.email_notifications' => ['sometimes', 'boolean'],
            'notification_preferences.sms_notifications' => ['sometimes', 'boolean'],
            'notification_preferences.push_notifications' => ['sometimes', 'boolean'],
            'notification_preferences.order_updates' => ['sometimes', 'boolean'],
            'notification_preferences.marketing_emails' => ['sometimes', 'boolean'],
            'notification_preferences.promotional_sms' => ['sometimes', 'boolean'],
            'notification_preferences.newsletter' => ['sometimes', 'boolean'],
            'system_alerts' => ['sometimes', 'boolean'],
            'emergency_alerts' => ['sometimes', 'boolean'],
        ];
    }
}
