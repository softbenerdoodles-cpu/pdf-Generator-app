<?php

namespace Tests\Unit;

use App\Documents\DocumentTemplate;
use App\Documents\Money;
use App\Services\Document\DocumentGenerator;
use App\Services\Templates\TemplateManager;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocumentTemplateTest extends TestCase
{
    public static function templates(): array
    {
        return collect((new TemplateManager)->all())
            ->mapWithKeys(fn (DocumentTemplate $t, string $key) => [$key => [$key]])
            ->all();
    }

    protected function template(string $key): DocumentTemplate
    {
        return app(TemplateManager::class)->get($key);
    }

    #[Test]
    #[DataProvider('templates')]
    public function its_sample_data_passes_its_own_validation_rules(string $key): void
    {
        $template = $this->template($key);

        $validator = Validator::make(['data' => $template->sample()], $template->rules());

        $this->assertTrue(
            $validator->passes(),
            "Sample data for [{$key}] failed: ".$validator->errors()->first()
        );
    }

    #[Test]
    #[DataProvider('templates')]
    public function it_renders_its_sample_data_to_a_pdf(string $key): void
    {
        $bytes = app(DocumentGenerator::class)->render($key, $this->template($key)->sample());

        $this->assertStringStartsWith('%PDF-', $bytes);
        $this->assertGreaterThan(1000, strlen($bytes));
    }

    #[Test]
    #[DataProvider('templates')]
    public function its_blank_document_has_a_key_for_every_field(string $key): void
    {
        $template = $this->template($key);
        $blank = $template->blank();

        foreach ($template->fields() as $field) {
            $this->assertArrayHasKey($field->name, $blank);
        }
    }

    #[Test]
    #[DataProvider('templates')]
    public function it_names_every_field_in_its_validation_messages(string $key): void
    {
        $template = $this->template($key);

        // Every rule path should have a readable label behind it.
        foreach (array_keys($template->rules()) as $path) {
            $this->assertArrayHasKey($path, $template->attributes(), "No label for [{$path}]");
        }
    }

    #[Test]
    public function it_totals_an_invoice_in_whole_cents(): void
    {
        $prepared = $this->template('invoice')->prepare([
            'currency' => 'USD',
            'tax_rate' => 20,
            'items' => [
                ['description' => 'Design', 'quantity' => 3, 'unit_price' => 0.1],
                ['description' => 'Build', 'quantity' => 1, 'unit_price' => 99.99],
            ],
        ]);

        // 0.10 x 3 + 99.99 = 100.29, which naive float addition gets wrong.
        $this->assertSame('$100.29', html_entity_decode($prepared['subtotal']));
        $this->assertSame('$20.06', html_entity_decode($prepared['tax']));
        $this->assertSame('$120.35', html_entity_decode($prepared['total']));
    }

    #[Test]
    public function it_takes_a_discount_off_before_tax(): void
    {
        $prepared = $this->template('invoice')->prepare([
            'currency' => 'USD',
            'tax_rate' => 10,
            'discount' => 100,
            'items' => [['description' => 'Work', 'quantity' => 1, 'unit_price' => 1100]],
        ]);

        $this->assertTrue($prepared['has_discount']);
        $this->assertSame('$100.00', html_entity_decode($prepared['tax']));
        $this->assertSame('$1,100.00', html_entity_decode($prepared['total']));
    }

    #[Test]
    public function a_discount_can_never_exceed_the_subtotal(): void
    {
        $prepared = $this->template('invoice')->prepare([
            'currency' => 'USD',
            'discount' => 5000,
            'items' => [['description' => 'Work', 'quantity' => 1, 'unit_price' => 100]],
        ]);

        $this->assertSame('$0.00', html_entity_decode($prepared['total']));
    }

    #[Test]
    public function it_splits_a_letter_body_into_paragraphs(): void
    {
        $prepared = $this->template('business-letter')->prepare([
            'body' => "First paragraph.\n\nSecond paragraph.\n\n\nThird.",
        ]);

        $this->assertCount(3, $prepared['paragraphs']);
    }

    #[Test]
    public function it_formats_money_without_float_drift(): void
    {
        $this->assertSame(1999, Money::toCents('19.99'));
        $this->assertSame(1999, Money::toCents(19.99));
        $this->assertSame('$1,234.56', html_entity_decode(Money::format(123456)));
        $this->assertSame('-$5.00', html_entity_decode(Money::format(-500)));
    }

    #[Test]
    public function every_template_declares_a_distinct_key_and_a_view_that_exists(): void
    {
        $manager = app(TemplateManager::class);

        $this->assertCount(8, $manager->keys());
        $this->assertSame($manager->keys(), array_unique($manager->keys()));

        foreach ($manager->all() as $template) {
            $this->assertTrue(view()->exists($template->view()), "Missing view for [{$template->key()}]");
        }
    }
}
