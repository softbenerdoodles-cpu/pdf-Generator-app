<?php

namespace App\Services\Document;

use App\Documents\DocumentTemplate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Checks submitted data against the chosen template's field schema.
 *
 * The rules come from the same Field definitions the React editor renders, so
 * a field can never be validated under different terms than it was collected.
 */
class DocumentValidator
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed> The validated data.
     *
     * @throws ValidationException
     */
    public function validate(DocumentTemplate $template, array $data): array
    {
        $validated = Validator::make(
            ['data' => $data],
            $template->rules(),
            [],
            $template->attributes(),
        )->validate();

        return $validated['data'] ?? [];
    }
}
