<?php

namespace App\Services\Document;

/**
 * What the parser understood from a prompt.
 *
 * The data is deliberately partial — it is merged over a template's blank
 * document and then handed to the editor for the user to finish.
 */
class ParsedDocument
{
    /**
     * @param  string  $template  Key of the chosen document type.
     * @param  array<string, mixed>  $data  Fields the parser filled in.
     * @param  list<string>  $understood  Plain-English notes shown back to the user.
     * @param  float  $confidence  0–1, how much of the prompt was recognised.
     */
    public function __construct(
        public readonly string $template,
        public readonly array $data,
        public readonly array $understood = [],
        public readonly float $confidence = 0.0,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'template' => $this->template,
            'data' => $this->data,
            'understood' => $this->understood,
            'confidence' => round($this->confidence, 2),
        ];
    }
}
