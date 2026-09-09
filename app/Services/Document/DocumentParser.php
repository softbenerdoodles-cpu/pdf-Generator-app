<?php

namespace App\Services\Document;

/**
 * Turns a free-text prompt into a partially filled document.
 *
 * The application binds RuleBasedParser to this contract. Swapping in a
 * model-backed parser later only means binding a different implementation.
 */
interface DocumentParser
{
    public function parse(string $prompt, ?string $preferredTemplate = null): ParsedDocument;
}
