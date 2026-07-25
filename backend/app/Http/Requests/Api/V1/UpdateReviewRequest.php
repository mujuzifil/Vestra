<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'comment' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'pros' => ['sometimes', 'nullable', 'array'],
            'pros.*' => ['string', 'max:255'],
            'cons' => ['sometimes', 'nullable', 'array'],
            'cons.*' => ['string', 'max:255'],
            'images' => ['sometimes', 'nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
