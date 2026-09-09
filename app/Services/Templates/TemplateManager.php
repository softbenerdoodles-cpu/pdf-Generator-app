<?php

namespace App\Services\Templates;

use App\Documents\DocumentTemplate;
use App\Documents\Templates\BusinessLetterTemplate;
use App\Documents\Templates\CertificateTemplate;
use App\Documents\Templates\ContractTemplate;
use App\Documents\Templates\EstimateTemplate;
use App\Documents\Templates\InvoiceTemplate;
use App\Documents\Templates\ProposalTemplate;
use App\Documents\Templates\QuoteTemplate;
use App\Documents\Templates\ReceiptTemplate;
use App\Documents\UnknownTemplateException;
use Illuminate\Support\Collection;

/**
 * The catalogue of document types the app can produce.
 *
 * Everything downstream — the type picker on the home page, the smart parser,
 * validation, and PDF rendering — resolves templates through here, so adding a
 * document type is a matter of writing one class and listing it below.
 */
class TemplateManager
{
    /** Registration order drives the order of the type tiles on the home page. */
    protected const DEFAULTS = [
        InvoiceTemplate::class,
        QuoteTemplate::class,
        ReceiptTemplate::class,
        EstimateTemplate::class,
        ProposalTemplate::class,
        ContractTemplate::class,
        BusinessLetterTemplate::class,
        CertificateTemplate::class,
    ];

    /** @var array<string, DocumentTemplate> */
    protected array $templates = [];

    /** @param  list<DocumentTemplate>|null  $templates */
    public function __construct(?array $templates = null)
    {
        foreach ($templates ?? array_map(fn (string $class) => new $class, self::DEFAULTS) as $template) {
            $this->register($template);
        }
    }

    public function register(DocumentTemplate $template): void
    {
        $this->templates[$template->key()] = $template;
    }

    public function has(string $key): bool
    {
        return isset($this->templates[$key]);
    }

    public function get(string $key): DocumentTemplate
    {
        return $this->templates[$key]
            ?? throw new UnknownTemplateException("Unknown document type [{$key}].");
    }

    /** @return Collection<string, DocumentTemplate> */
    public function all(): Collection
    {
        return collect($this->templates);
    }

    /** @return list<string> */
    public function keys(): array
    {
        return array_keys($this->templates);
    }

    /**
     * Catalogue entries for the type picker — no field schemas, so it stays
     * cheap to share with every page.
     *
     * @return list<array<string, mixed>>
     */
    public function summaries(): array
    {
        return $this->all()->map(fn (DocumentTemplate $t) => $t->summary())->values()->all();
    }
}
