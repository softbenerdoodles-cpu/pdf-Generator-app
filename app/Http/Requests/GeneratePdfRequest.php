<?php

namespace App\Http\Requests;

use App\Services\Templates\TemplateManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Only the envelope is checked here — the document body is validated against
 * the chosen template's own field schema by DocumentValidator.
 */
class GeneratePdfRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'template' => ['required', 'string', Rule::in(app(TemplateManager::class)->keys())],
            'data' => ['present', 'array'],
            'save' => ['sometimes', 'boolean'],
        ];
    }

    public function templateKey(): string
    {
        return $this->string('template')->value();
    }

    /** @return array<string, mixed> */
    public function documentData(): array
    {
        return $this->array('data');
    }
}
