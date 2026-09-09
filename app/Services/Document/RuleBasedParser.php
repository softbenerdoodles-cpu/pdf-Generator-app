<?php

namespace App\Services\Document;

use App\Documents\DocumentTemplate;
use App\Documents\Money;
use App\Services\Templates\TemplateManager;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Reads a prompt with regular expressions rather than a language model.
 *
 * It is deliberately modest: it recognises the document type, who it is for,
 * money, quantities and dates, and leaves everything else to the editor. That
 * keeps generation instant, free, and private — no prompt ever leaves the box.
 */
class RuleBasedParser implements DocumentParser
{
    protected const MONTHS = 'january|february|march|april|may|june|july|august|september|october|november|december|jan|feb|mar|apr|jun|jul|aug|sept|sep|oct|nov|dec';

    /** A date written any of the usual ways. */
    protected const DATE = '(?:\d{4}-\d{1,2}-\d{1,2}'
        .'|\d{1,2}[\/.]\d{1,2}[\/.]\d{2,4}'
        .'|(?:'.self::MONTHS.')\s+\d{1,2}(?:st|nd|rd|th)?(?:,?\s*\d{4})?'
        .'|\d{1,2}(?:st|nd|rd|th)?\s+(?:of\s+)?(?:'.self::MONTHS.')(?:,?\s*\d{4})?)';

    /** An amount with either a leading symbol or a trailing currency word. */
    protected const MONEY = '(?:[$£€¥]\s?\d[\d,]*(?:\.\d{1,2})?'
        .'|\d[\d,]*(?:\.\d{1,2})?\s?(?:dollars?|usd|pounds?|gbp|euros?|eur|yen|jpy|cad|aud)\b)';

    protected const SYMBOL_CURRENCIES = ['$' => 'USD', '£' => 'GBP', '€' => 'EUR', '¥' => 'JPY'];

    protected const WORD_CURRENCIES = [
        'dollar' => 'USD', 'dollars' => 'USD', 'usd' => 'USD',
        'pound' => 'GBP', 'pounds' => 'GBP', 'gbp' => 'GBP',
        'euro' => 'EUR', 'euros' => 'EUR', 'eur' => 'EUR',
        'yen' => 'JPY', 'jpy' => 'JPY', 'cad' => 'CAD', 'aud' => 'AUD',
    ];

    /** Capitalised words that are never a person or company. */
    protected const NOT_NAMES = [
        'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august',
        'september', 'october', 'november', 'december', 'monday', 'tuesday',
        'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'today',
        'tomorrow', 'the', 'a', 'an', 'my', 'our', 'their', 'his', 'her',
    ];

    protected const NUMBER_PREFIXES = [
        'invoice' => 'INV',
        'quote' => 'QUO',
        'estimate' => 'EST',
        'receipt' => 'RCP',
        'proposal' => 'PRO',
        'contract' => 'AGR',
        'certificate' => 'CERT',
    ];

    public function __construct(protected TemplateManager $templates) {}

    public function parse(string $prompt, ?string $preferredTemplate = null): ParsedDocument
    {
        $prompt = trim((string) preg_replace('/\s+/', ' ', $prompt));

        $template = $preferredTemplate && $this->templates->has($preferredTemplate)
            ? $this->templates->get($preferredTemplate)
            : $this->templates->get($this->detectTemplate($prompt) ?? 'invoice');

        $found = [
            'type' => $preferredTemplate !== null || $this->detectTemplate($prompt) !== null,
            'party' => ($party = $this->extractParty($prompt)) !== null,
            'items' => ($items = $this->extractItems($prompt)) !== [],
            'date' => ($dates = $this->extractDates($prompt)) !== [],
        ];

        $currency = $this->extractCurrency($prompt) ?? 'USD';
        $number = $this->extractReference($prompt) ?? $this->generateReference($template->key());

        $data = $this->buildData($template, [
            'party' => $party,
            'items' => $items,
            'dates' => $dates,
            'currency' => $currency,
            'number' => $number,
            'prompt' => $prompt,
        ]);

        return new ParsedDocument(
            template: $template->key(),
            data: $data,
            understood: $this->describe($template, $party, $items, $dates, $currency),
            confidence: count(array_filter($found)) / count($found),
        );
    }

    /* ---------------------------------------------------------------- type */

    protected function detectTemplate(string $prompt): ?string
    {
        $haystack = Str::lower($prompt);
        $best = null;
        $bestPosition = PHP_INT_MAX;

        foreach ($this->templates->all() as $key => $template) {
            foreach ($this->needles($key, $template) as $needle) {
                $position = mb_strpos($haystack, $needle);

                if ($position !== false && $position < $bestPosition) {
                    $best = $key;
                    $bestPosition = $position;
                }
            }
        }

        return $best;
    }

    /** @return list<string> */
    protected function needles(string $key, DocumentTemplate $template): array
    {
        return array_merge(
            [str_replace('-', ' ', $key), Str::lower($template->name())],
            $template->keywords(),
        );
    }

    /* ------------------------------------------------------------ currency */

    protected function extractCurrency(string $prompt): ?string
    {
        if (preg_match('/([$£€¥])/u', $prompt, $m)) {
            return self::SYMBOL_CURRENCIES[$m[1]];
        }

        if (preg_match('/\b(dollars?|usd|pounds?|gbp|euros?|eur|yen|jpy|cad|aud)\b/i', $prompt, $m)) {
            return self::WORD_CURRENCIES[Str::lower($m[1])] ?? null;
        }

        return null;
    }

    protected function toAmount(string $money): float
    {
        return (float) str_replace(',', '', (string) preg_replace('/[^\d.,]/', '', $money));
    }

    /* --------------------------------------------------------------- items */

    /**
     * Pull out priced lines. Recognises "3 x logo design at $200",
     * "$850 for logo design", and "logo design for $850".
     *
     * @return list<array{description: string, quantity: float, unit_price: float}>
     */
    protected function extractItems(string $prompt): array
    {
        $items = [];
        $consumed = [];

        // "3 x logo design at $200" / "12 hours of consulting at $145"
        $pattern = '/(\d+(?:\.\d+)?)\s*(?:x|×|hours?\s+of|units?\s+of|days?\s+of)\s*([^,;.]+?)\s*(?:at|@|for)\s*('.self::MONEY.')/iu';
        if (preg_match_all($pattern, $prompt, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $items[] = [
                    'description' => $this->cleanDescription($m[2]),
                    'quantity' => (float) $m[1],
                    'unit_price' => $this->toAmount($m[3]),
                ];
                $consumed[] = $m[0];
            }
        }

        $remaining = str_replace($consumed, ' ', $prompt);

        // "$850 for logo design"
        $pattern = '/('.self::MONEY.')\s+(?:for|covering|towards)\s+([^,;.]+?)(?=\s*(?:,|;|\.|$|\band\b|\bdue\b|\bpayable\b|\bvalid\b))/iu';
        if (preg_match_all($pattern, $remaining, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $items[] = [
                    'description' => $this->cleanDescription($m[2]),
                    'quantity' => 1,
                    'unit_price' => $this->toAmount($m[1]),
                ];
                $consumed[] = $m[0];
            }
        }

        $remaining = str_replace($consumed, ' ', $prompt);

        // "logo design for $850"
        $pattern = '/(?:for|covering)\s+([a-z][^,;.]*?)\s+(?:for|at|@|costing)\s+('.self::MONEY.')/iu';
        if (preg_match_all($pattern, $remaining, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $items[] = [
                    'description' => $this->cleanDescription($m[1]),
                    'quantity' => 1,
                    'unit_price' => $this->toAmount($m[2]),
                ];
                $consumed[] = $m[0];
            }
        }

        // A bare amount with nothing describing it still deserves a line.
        if ($items === []) {
            $remaining = str_replace($consumed, ' ', $prompt);

            if (preg_match('/('.self::MONEY.')/u', $remaining, $m)) {
                $items[] = [
                    'description' => $this->fallbackDescription($prompt),
                    'quantity' => 1,
                    'unit_price' => $this->toAmount($m[1]),
                ];
            }
        }

        return $items;
    }

    protected function cleanDescription(string $description): string
    {
        $description = trim($description, " \t\n\r\0\x0B-–—,;:");
        $description = (string) preg_replace('/^(?:the|a|an|some)\s+/i', '', $description);
        $description = (string) preg_replace('/\s+(?:due|payable|valid)\b.*$/i', '', $description);

        return Str::ucfirst(trim($description));
    }

    /** Last resort: the noun phrase after "for", minus names and amounts. */
    protected function fallbackDescription(string $prompt): string
    {
        if (preg_match('/\bfor\s+([a-z][^,;.]{2,60})/i', $prompt, $m)) {
            return $this->cleanDescription($m[1]);
        }

        return 'Services';
    }

    /* --------------------------------------------------------------- party */

    protected function extractParty(string $prompt): ?string
    {
        $patterns = [
            '/\b(?:bill(?:ed)?\s+to|made\s+out\s+to|addressed\s+to|for\s+client|for\s+customer|client|customer)\s+((?:[A-Z][\w&\'’.-]*)(?:\s+(?:of|and|&|[A-Z][\w&\'’.-]*)){0,4})/u',
            '/\b(?:for|to)\s+((?:[A-Z][\w&\'’.-]*)(?:\s+(?:of|and|&|[A-Z][\w&\'’.-]*)){0,4})/u',
        ];

        foreach ($patterns as $pattern) {
            if (! preg_match_all($pattern, $prompt, $matches, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($matches as $m) {
                $name = $this->trimName($m[1]);

                if ($name !== null) {
                    return $name;
                }
            }
        }

        return null;
    }

    /** Drop trailing filler and reject anything that is not really a name. */
    protected function trimName(string $name): ?string
    {
        $words = preg_split('/\s+/', trim($name)) ?: [];

        while ($words !== [] && in_array(Str::lower(end($words)), ['of', 'and', '&'], true)) {
            array_pop($words);
        }

        if ($words === []) {
            return null;
        }

        $name = implode(' ', $words);

        if (in_array(Str::lower($words[0]), self::NOT_NAMES, true)) {
            return null;
        }

        return trim($name, " .,'’-") ?: null;
    }

    /* --------------------------------------------------------------- dates */

    /** @return array<string, CarbonImmutable> */
    protected function extractDates(string $prompt): array
    {
        $dates = [];
        $today = CarbonImmutable::today();

        if (preg_match('/\b(?:due|payable|expires?|expiring)\s+in\s+(\d{1,3})\s*(day|week|month)s?\b/i', $prompt, $m)) {
            $dates['due'] = $today->add(Str::lower($m[2]).'s', (int) $m[1]);
        } elseif (preg_match('/\b(?:due|payable|deadline)(?:\s+(?:on|by|before))?\s+('.self::DATE.')/iu', $prompt, $m)) {
            $dates['due'] = $this->toDate($m[1], forward: true);
        } elseif (preg_match('/\bby\s+('.self::DATE.')/iu', $prompt, $m)) {
            $dates['due'] = $this->toDate($m[1], forward: true);
        }

        if (preg_match('/\bvalid\s+(?:until|through|till|to)\s+('.self::DATE.')/iu', $prompt, $m)) {
            $dates['due'] = $this->toDate($m[1], forward: true);
        }

        if (preg_match('/\b(?:dated|issued(?:\s+on)?|on)\s+('.self::DATE.')/iu', $prompt, $m)) {
            $issued = $this->toDate($m[1], forward: false);

            if ($issued !== null && (! isset($dates['due']) || ! $issued->equalTo($dates['due']))) {
                $dates['issue'] = $issued;
            }
        }

        return array_filter($dates);
    }

    /**
     * Parse a matched date. A date with no year that has already passed is
     * read as next year when it is a deadline.
     */
    protected function toDate(string $raw, bool $forward): ?CarbonImmutable
    {
        $raw = trim((string) preg_replace('/(\d)(st|nd|rd|th)/i', '$1', $raw));

        try {
            $date = CarbonImmutable::parse($raw);
        } catch (\Throwable) {
            return null;
        }

        $hasYear = (bool) preg_match('/\d{4}|\d{1,2}[\/.]\d{1,2}[\/.]\d{2,4}/', $raw);

        if ($forward && ! $hasYear && $date->lessThan(CarbonImmutable::today())) {
            $date = $date->addYear();
        }

        return $date;
    }

    protected function extractReference(string $prompt): ?string
    {
        return preg_match('/\b([A-Z]{2,4}-[A-Z0-9]*\d[A-Z0-9-]*)\b/', $prompt, $m) ? $m[1] : null;
    }

    protected function generateReference(string $templateKey): string
    {
        $prefix = self::NUMBER_PREFIXES[$templateKey] ?? 'DOC';

        return $prefix.'-'.CarbonImmutable::today()->year.'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
    }

    /* ---------------------------------------------------------- assembling */

    /**
     * @param  array<string, mixed>  $parts
     * @return array<string, mixed>
     */
    protected function buildData(DocumentTemplate $template, array $parts): array
    {
        $blank = $template->blank();
        $today = CarbonImmutable::today();
        $party = $parts['party'];
        $items = $parts['items'];
        $dates = $parts['dates'];

        $issue = ($dates['issue'] ?? $today)->toDateString();
        $due = $dates['due'] ?? null;

        $data = match ($template->key()) {
            'invoice', 'quote', 'estimate' => [
                'number' => $parts['number'],
                'currency' => $parts['currency'],
                'issue_date' => $issue,
                $template->key() === 'invoice' ? 'due_date' : 'valid_until' => ($due ?? $today->addDays(
                    $template->key() === 'invoice' ? 21 : 30
                ))->toDateString(),
                'to' => ['name' => $party ?? ''],
                'items' => $items ?: null,
            ],
            'receipt' => [
                'number' => $parts['number'],
                'currency' => $parts['currency'],
                'paid_on' => $issue,
                'payer' => $party ?? '',
                'items' => $items
                    ? array_map(fn (array $i) => [
                        'description' => $i['description'],
                        'amount' => $i['unit_price'] * $i['quantity'],
                    ], $items)
                    : null,
            ],
            'proposal' => [
                'number' => $parts['number'],
                'currency' => $parts['currency'],
                'issue_date' => $issue,
                'valid_until' => ($due ?? $today->addDays(30))->toDateString(),
                'title' => $this->subjectFrom($parts['prompt'], $items),
                'to' => ['name' => $party ?? ''],
                'deliverables' => $items
                    ? array_map(fn (array $i) => [
                        'description' => $i['description'],
                        'amount' => $i['unit_price'] * $i['quantity'],
                    ], $items)
                    : null,
            ],
            'contract' => [
                'reference' => $parts['number'],
                'effective_date' => $issue,
                'title' => $this->subjectFrom($parts['prompt'], $items),
                'party_two' => ['name' => $party ?? ''],
            ],
            'business-letter' => [
                'written_on' => $issue,
                'recipient' => ['name' => $party ?? ''],
                'subject' => $this->subjectFrom($parts['prompt'], $items),
            ],
            'certificate' => [
                'recipient' => $party ?? '',
                'awarded_on' => $issue,
                'reference' => $parts['number'],
            ],
            default => [],
        };

        return $this->merge($blank, array_filter($data, fn ($value) => $value !== null));
    }

    /** A short title drawn from what the prompt said the work was. */
    protected function subjectFrom(string $prompt, array $items): string
    {
        if ($items !== []) {
            return $items[0]['description'];
        }

        if (preg_match('/\b(?:about|regarding|re:|concerning|for)\s+([a-z][^,;.]{2,70})/i', $prompt, $m)) {
            return $this->cleanDescription($m[1]);
        }

        return '';
    }

    /**
     * Overlay parsed values on the blank document without dropping the keys
     * the editor expects to find.
     *
     * @param  array<string, mixed>  $blank
     * @param  array<string, mixed>  $found
     * @return array<string, mixed>
     */
    protected function merge(array $blank, array $found): array
    {
        foreach ($found as $key => $value) {
            if (is_array($value) && Arr::isAssoc($value) && isset($blank[$key]) && is_array($blank[$key])) {
                $blank[$key] = $this->merge($blank[$key], $value);

                continue;
            }

            $blank[$key] = $value;
        }

        return $blank;
    }

    /** @return list<string> */
    protected function describe(
        DocumentTemplate $template,
        ?string $party,
        array $items,
        array $dates,
        string $currency,
    ): array {
        $notes = ['Document type: '.$template->name()];

        if ($party !== null) {
            $notes[] = 'For: '.$party;
        }

        if ($items !== []) {
            $total = 0;

            foreach ($items as $item) {
                $total += (int) round(Money::toCents($item['unit_price']) * $item['quantity']);
            }

            $notes[] = count($items).' '.Str::plural('line item', count($items))
                .' totalling '.html_entity_decode(Money::format($total, $currency));
        }

        if (isset($dates['due'])) {
            $notes[] = 'Dated: '.$dates['due']->format('j F Y');
        }

        return $notes;
    }
}
