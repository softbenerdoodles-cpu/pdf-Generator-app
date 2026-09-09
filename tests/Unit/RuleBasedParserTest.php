<?php

namespace Tests\Unit;

use App\Services\Document\RuleBasedParser;
use App\Services\Templates\TemplateManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RuleBasedParserTest extends TestCase
{
    protected RuleBasedParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new RuleBasedParser(new TemplateManager);
    }

    #[Test]
    public function it_reads_the_example_prompt_from_the_home_page(): void
    {
        $parsed = $this->parser->parse(
            'Create an invoice for John Smith for $850 for logo design, due September 30'
        );

        $this->assertSame('invoice', $parsed->template);
        $this->assertSame('John Smith', $parsed->data['to']['name']);
        $this->assertCount(1, $parsed->data['items']);
        $this->assertSame('Logo design', $parsed->data['items'][0]['description']);
        $this->assertSame(850.0, $parsed->data['items'][0]['unit_price']);
        $this->assertSame(now()->year.'-09-30', $parsed->data['due_date']);
        $this->assertSame(1.0, $parsed->confidence);
    }

    #[Test]
    #[DataProvider('promptTypes')]
    public function it_picks_the_document_type_from_the_words_used(string $prompt, string $expected): void
    {
        $this->assertSame($expected, $this->parser->parse($prompt)->template);
    }

    public static function promptTypes(): array
    {
        return [
            'quote' => ['Quote for Acme Ltd', 'quote'],
            'receipt' => ['Receipt for Acme Ltd for $50', 'receipt'],
            'estimate' => ['Write an estimate for the roof work', 'estimate'],
            'proposal' => ['Draft a proposal for Bright Ideas', 'proposal'],
            'contract' => ['Prepare a contract with Bright Ideas', 'contract'],
            'letter' => ['A business letter to Jane Doe', 'business-letter'],
            'certificate' => ['Certificate of completion for Jordan Ellis', 'certificate'],
            'synonym' => ['Bill Acme Ltd for consulting', 'invoice'],
            'fallback' => ['Something entirely unrelated', 'invoice'],
        ];
    }

    #[Test]
    public function it_honours_an_explicitly_chosen_type_over_the_words_used(): void
    {
        $parsed = $this->parser->parse('invoice for Acme Ltd for $200', 'receipt');

        $this->assertSame('receipt', $parsed->template);
        $this->assertSame('Acme Ltd', $parsed->data['payer']);
    }

    #[Test]
    public function it_reads_quantities_priced_per_unit(): void
    {
        $item = $this->parser->parse('Invoice Acme for 12 hours of consulting at $145')->data['items'][0];

        $this->assertSame('Consulting', $item['description']);
        $this->assertSame(12.0, $item['quantity']);
        $this->assertSame(145.0, $item['unit_price']);
    }

    #[Test]
    public function it_reads_several_priced_lines_from_one_sentence(): void
    {
        $parsed = $this->parser->parse(
            'Quote for Bright Ideas Co. — website design $1,200 and SEO setup $300, valid until October 15'
        );

        $this->assertSame('quote', $parsed->template);
        $this->assertSame(now()->year.'-10-15', $parsed->data['valid_until']);
        $this->assertContains(1200.0, array_column($parsed->data['items'], 'unit_price'));
    }

    #[Test]
    #[DataProvider('currencies')]
    public function it_picks_up_the_currency_from_the_symbol_used(string $prompt, string $currency): void
    {
        $this->assertSame($currency, $this->parser->parse($prompt)->data['currency']);
    }

    public static function currencies(): array
    {
        return [
            ['Invoice Acme for £500', 'GBP'],
            ['Invoice Acme for €500', 'EUR'],
            ['Invoice Acme for 500 dollars', 'USD'],
            ['Invoice Acme for $500', 'USD'],
        ];
    }

    #[Test]
    public function it_reads_a_deadline_given_as_a_number_of_days(): void
    {
        $parsed = $this->parser->parse('Invoice Acme Ltd for $100 due in 14 days');

        $this->assertSame(now()->addDays(14)->toDateString(), $parsed->data['due_date']);
    }

    #[Test]
    public function it_rolls_a_bare_date_forward_when_it_has_already_passed(): void
    {
        $parsed = $this->parser->parse('Invoice Acme for $100 due January 5');

        $this->assertGreaterThan(now()->toDateString(), $parsed->data['due_date']);
    }

    #[Test]
    public function it_never_mistakes_a_month_for_a_customer_name(): void
    {
        $parsed = $this->parser->parse('Invoice for September 30 for $100');

        $this->assertNotSame('September 30', $parsed->data['to']['name']);
    }

    #[Test]
    public function it_generates_a_reference_when_the_prompt_has_none(): void
    {
        $this->assertStringStartsWith('INV-', $this->parser->parse('Invoice for Acme')->data['number']);
    }

    #[Test]
    public function it_keeps_a_reference_the_prompt_already_supplies(): void
    {
        $this->assertSame('INV-4242', $this->parser->parse('Invoice INV-4242 for Acme')->data['number']);
    }

    #[Test]
    public function it_returns_a_complete_document_even_from_a_thin_prompt(): void
    {
        $parsed = $this->parser->parse('an invoice');

        foreach (['number', 'issue_date', 'due_date', 'currency', 'from', 'to', 'items'] as $key) {
            $this->assertArrayHasKey($key, $parsed->data);
        }

        $this->assertLessThan(1.0, $parsed->confidence);
    }
}
