<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'target_audience' => ['required', 'string', 'in:everyone,customers,distributors,admins'],
            'priority' => ['nullable', 'string', 'in:low,normal,high,critical'],
            'pinned' => ['sometimes', 'boolean'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'sent_at' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date'],
        ];
    }
}
