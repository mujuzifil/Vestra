<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\FeedbackCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', Rule::enum(FeedbackCategory::class)],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'subject' => ['required', 'string', 'min:3', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Please select a feedback category.',
            'category.in' => 'The selected feedback category is invalid.',
            'rating.integer' => 'Rating must be a whole number.',
            'rating.min' => 'Rating must be at least 1.',
            'rating.max' => 'Rating cannot exceed 5.',
            'subject.required' => 'Please provide a subject.',
            'message.required' => 'Please enter your feedback message.',
        ];
    }
}
