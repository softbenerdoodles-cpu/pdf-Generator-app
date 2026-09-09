<?php

namespace App\Documents;

/**
 * A kind of document the application knows how to produce.
 *
 * A template owns its input schema, its Blade view, the words the smart parser
 * uses to recognise it, and any figures derived from the submitted data.
 */
abstract class DocumentTemplate
{
    /** Stable identifier used in URLs, the API, and stored documents. */
    abstract public function key(): string;

    abstract public function name(): string;

    abstract public function description(): string;

    /** Lucide icon name rendered on the type tile. */
    abstract public function icon(): string;

    /** Accent colour token for the type tile. */
    abstract public function accent(): string;

    /** @return list<Field> */
    abstract public function fields(): array;

    /**
     * Realistic values behind the "Load example" button.
     *
     * @return array<string, mixed>
     */
    abstract public function sample(): array;

    /** Human readable name for a generated document. */
    abstract public function title(array $data): string;

    /**
     * Words that make the smart parser choose this template. The key itself and
     * the lower-cased name are always matched, so this is only for synonyms.
     *
     * @return list<string>
     */
    public function keywords(): array
    {
        return [];
    }

    /** The accent colour as hex, for the PDF views. */
    public function accentHex(): string
    {
        return match ($this->accent()) {
            'green' => '#15803d',
            'purple' => '#6d28d9',
            'orange' => '#c2410c',
            'pink' => '#be185d',
            'sky' => '#0369a1',
            'indigo' => '#4338ca',
            'amber' => '#b45309',
            default => '#1d4ed8',
        };
    }

    public function view(): string
    {
        return 'pdf.'.$this->key();
    }

    public function paperSize(): string
    {
        return config('pdf.paper.size');
    }

    public function paperOrientation(): string
    {
        return config('pdf.paper.orientation');
    }

    /**
     * Validation rules for a submission, compiled from the field schema.
     *
     * @return array<string, list<string>>
     */
    public function rules(string $prefix = 'data.'): array
    {
        $rules = [];

        foreach ($this->fields() as $field) {
            $rules += $field->rules($prefix);
        }

        return $rules;
    }

    /**
     * Human field names for validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(string $prefix = 'data.'): array
    {
        $attributes = [];

        foreach ($this->fields() as $field) {
            $attributes += $field->attributes($prefix);
        }

        return $attributes;
    }

    /**
     * An empty document: every field present, so React controls stay
     * controlled and the editor never reads an undefined value.
     *
     * @return array<string, mixed>
     */
    public function blank(): array
    {
        $blank = [];

        foreach ($this->fields() as $field) {
            $blank[$field->name] = $field->blankValue();
        }

        return $blank;
    }

    /**
     * Hook for values the view needs but the user does not type — subtotals,
     * tax, grand totals. Returns the data the Blade view is rendered with.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepare(array $data): array
    {
        return $data;
    }

    /**
     * Catalogue entry for the type picker.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'key' => $this->key(),
            'name' => $this->name(),
            'description' => $this->description(),
            'icon' => $this->icon(),
            'accent' => $this->accent(),
        ];
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->summary() + [
            'fields' => array_map(fn (Field $field) => $field->toArray(), $this->fields()),
            'blank' => $this->blank(),
        ];
    }
}
