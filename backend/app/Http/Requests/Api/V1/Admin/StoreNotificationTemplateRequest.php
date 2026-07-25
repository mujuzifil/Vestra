<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_key' => ['required', 'string', 'max:255', 'unique:notification_templates,event_key'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'subject' => ['nullable', 'string', 'max:255'],
            'email_body' => ['nullable', 'string'],
            'sms_body' => ['nullable', 'string'],
            'in_app_body' => ['nullable', 'string'],
            'channels_json' => ['nullable', 'array'],
            'channels_json.*' => ['string', 'in:email,sms,in_app,push,whatsapp,webhook'],
            'variables_json' => ['nullable', 'array'],
            'variables_json.*' => ['string'],
            'priority' => ['nullable', 'string', 'in:low,normal,high,critical'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
