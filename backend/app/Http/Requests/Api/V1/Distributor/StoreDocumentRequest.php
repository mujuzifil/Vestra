<?php

namespace App\Http\Requests\Api\V1\Distributor;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'file' => ['required', 'file', 'max:10240'],
            'version' => ['sometimes', 'string', 'max:50'],
        ];
    }
}
