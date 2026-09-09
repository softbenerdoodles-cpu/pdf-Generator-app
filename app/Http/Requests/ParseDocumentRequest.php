<?php

namespace App\Http\Requests;

use App\Services\Templates\TemplateManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParseDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'min:3', 'max:2000'],
            'template' => ['nullable', 'string', Rule::in(app(TemplateManager::class)->keys())],
        ];
    }

    public function messages(): array
    {
        return [
            'prompt.required' => 'Describe the document you want and we will draft it.',
            'prompt.min' => 'Add a little more detail so we can work out what you need.',
        ];
    }
}
