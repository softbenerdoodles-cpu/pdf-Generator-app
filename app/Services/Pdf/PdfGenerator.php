<?php

namespace App\Services\Pdf;

use App\Documents\DocumentTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renders a template's Blade view to PDF bytes.
 *
 * Everything the document shows is passed in as already-prepared data, so the
 * views stay presentational and this class stays the only place that talks to
 * the PDF engine.
 */
class PdfGenerator
{
    /**
     * @param  array<string, mixed>  $data  Output of the template's prepare().
     */
    public function render(DocumentTemplate $template, array $data): string
    {
        return $this->make($template, $data)->output();
    }

    /** Show the PDF in the browser's viewer. */
    public function stream(DocumentTemplate $template, array $data, string $filename): Response
    {
        return $this->make($template, $data)->stream($filename);
    }

    /** Send the PDF as a file download. */
    public function download(DocumentTemplate $template, array $data, string $filename): Response
    {
        return $this->make($template, $data)->download($filename);
    }

    protected function make(DocumentTemplate $template, array $data): DomPdf
    {
        return Pdf::setPaper($template->paperSize(), $template->paperOrientation())
            ->setOptions([
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 96,
            ])
            ->loadView($template->view(), [
                'document' => $data,
                'template' => $template,
            ]);
    }
}
