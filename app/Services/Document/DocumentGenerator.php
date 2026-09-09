<?php

namespace App\Services\Document;

use App\Documents\DocumentTemplate;
use App\Models\Document;
use App\Services\Pdf\PdfGenerator;
use App\Services\Templates\TemplateManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * The one route from "here is some data" to "here is a PDF".
 *
 * Validation, derived totals, and rendering happen here so the controllers,
 * the API, and any future queued job all produce identical output.
 */
class DocumentGenerator
{
    public function __construct(
        protected TemplateManager $templates,
        protected DocumentValidator $validator,
        protected PdfGenerator $pdf,
    ) {}

    public function template(string $key): DocumentTemplate
    {
        return $this->templates->get($key);
    }

    /**
     * Validate raw input and fold in the figures the view needs.
     *
     * @param  array<string, mixed>  $data
     * @return array{template: DocumentTemplate, data: array<string, mixed>, prepared: array<string, mixed>}
     *
     * @throws ValidationException
     */
    public function prepare(string $templateKey, array $data): array
    {
        $template = $this->template($templateKey);
        $validated = $this->validator->validate($template, $data);

        return [
            'template' => $template,
            'data' => $validated,
            'prepared' => $template->prepare($validated),
        ];
    }

    /** Raw PDF bytes. */
    public function render(string $templateKey, array $data): string
    {
        ['template' => $template, 'prepared' => $prepared] = $this->prepare($templateKey, $data);

        return $this->pdf->render($template, $prepared);
    }

    /** Inline response for the preview pane. */
    public function stream(string $templateKey, array $data): Response
    {
        ['template' => $template, 'data' => $validated, 'prepared' => $prepared] = $this->prepare($templateKey, $data);

        return $this->pdf->stream($template, $prepared, $this->filename($template, $validated));
    }

    /** Attachment response for the download button. */
    public function download(string $templateKey, array $data): Response
    {
        ['template' => $template, 'data' => $validated, 'prepared' => $prepared] = $this->prepare($templateKey, $data);

        return $this->pdf->download($template, $prepared, $this->filename($template, $validated));
    }

    /**
     * Keep a copy for a signed-in user. Guests never reach this — their data
     * is rendered and discarded within the request.
     */
    public function store(int $userId, string $templateKey, array $data): Document
    {
        ['template' => $template, 'data' => $validated, 'prepared' => $prepared] = $this->prepare($templateKey, $data);

        $bytes = $this->pdf->render($template, $prepared);
        $path = config('pdf.directory').'/'.$userId.'/'.Str::uuid().'.pdf';

        Storage::disk(config('pdf.disk'))->put($path, $bytes);

        return Document::create([
            'user_id' => $userId,
            'template' => $template->key(),
            'title' => $this->title($template, $validated),
            'data' => $validated,
            'path' => $path,
            'size' => strlen($bytes),
            'checksum' => hash('sha256', $bytes),
        ]);
    }

    public function title(DocumentTemplate $template, array $data): string
    {
        return trim($template->title($data)) ?: $template->name();
    }

    public function filename(DocumentTemplate $template, array $data): string
    {
        $slug = Str::slug($this->title($template, $data));

        return ($slug ?: $template->key()).'.pdf';
    }
}
