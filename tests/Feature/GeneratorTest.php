<?php

namespace Tests\Feature;

use App\Services\Templates\TemplateManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GeneratorTest extends TestCase
{
    use RefreshDatabase;

    public static function templates(): array
    {
        return collect((new TemplateManager)->keys())
            ->mapWithKeys(fn (string $key) => [$key => [$key]])
            ->all();
    }

    #[Test]
    public function the_home_page_offers_every_document_type(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Home')
                ->has('types', 8)
                ->has('examples')
            );
    }

    #[Test]
    #[DataProvider('templates')]
    public function the_editor_opens_for_each_type_without_signing_in(string $key): void
    {
        $this->get("/generate/{$key}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Generator/Create')
                ->where('template.key', $key)
                ->has('template.fields')
                ->has('template.blank')
                ->has('sample')
                ->where('business', null)
                ->where('customers', [])
            );
    }

    #[Test]
    public function an_unknown_document_type_is_a_404(): void
    {
        $this->get('/generate/hovercraft')->assertNotFound();
    }

    #[Test]
    public function a_prompt_lands_the_visitor_in_the_editor_with_a_draft(): void
    {
        $response = $this->post('/generate/parse', [
            'prompt' => 'Create an invoice for John Smith for $850 for logo design, due September 30',
        ]);

        $response->assertRedirect('/generate/invoice');
        $response->assertSessionHas('parsed');

        $parsed = session('parsed');

        $this->assertSame('invoice', $parsed['template']);
        $this->assertSame('John Smith', $parsed['data']['to']['name']);
        $this->assertNotEmpty($parsed['understood']);
    }

    #[Test]
    public function a_prompt_that_says_nothing_is_rejected(): void
    {
        $this->post('/generate/parse', ['prompt' => 'hi'])
            ->assertSessionHasErrors('prompt');
    }

    #[Test]
    #[DataProvider('templates')]
    public function it_streams_a_preview_for_each_type(string $key): void
    {
        $sample = app(TemplateManager::class)->get($key)->sample();

        $response = $this->post('/pdf/preview', ['template' => $key, 'data' => $sample]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    #[Test]
    public function it_downloads_a_pdf_named_after_the_document(): void
    {
        $sample = app(TemplateManager::class)->get('invoice')->sample();

        $this->post('/pdf/download', ['template' => 'invoice', 'data' => $sample])
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('invoice-inv-1001.pdf');
    }

    #[Test]
    public function a_document_missing_required_details_is_answered_with_json_not_a_redirect(): void
    {
        // A redirect here would be followed by fetch and rendered as a document.
        $response = $this->postJson('/pdf/preview', ['template' => 'invoice', 'data' => []]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['data.number', 'data.from.name']);
    }

    #[Test]
    public function it_rejects_a_document_type_it_does_not_know(): void
    {
        $this->postJson('/pdf/preview', ['template' => 'hovercraft', 'data' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors('template');
    }

    #[Test]
    public function validation_messages_name_fields_the_way_the_form_labels_them(): void
    {
        $response = $this->postJson('/pdf/preview', ['template' => 'invoice', 'data' => []]);

        $this->assertSame(
            'The business name field is required.',
            $response->json('errors')['data.from.name'][0]
        );
    }

    #[Test]
    public function the_full_page_preview_echoes_the_draft_back_without_storing_it(): void
    {
        $sample = app(TemplateManager::class)->get('invoice')->sample();

        $this->post('/generate/preview', ['template' => 'invoice', 'data' => $sample])
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Generator/Preview')
                ->where('title', 'Invoice INV-1001')
                ->where('filename', 'invoice-inv-1001.pdf')
            );

        $this->assertDatabaseCount('documents', 0);
    }

    #[Test]
    public function generating_as_a_guest_never_writes_a_document_row(): void
    {
        $sample = app(TemplateManager::class)->get('receipt')->sample();

        $this->post('/pdf/download', ['template' => 'receipt', 'data' => $sample])->assertOk();

        $this->assertDatabaseCount('documents', 0);
    }
}
