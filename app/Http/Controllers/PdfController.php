<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneratePdfRequest;
use App\Services\Document\DocumentGenerator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Turns a draft into a PDF.
 *
 * Nothing is written to disk on these routes: the bytes are built in memory
 * and streamed straight back, which is what lets the app promise that guest
 * documents are not stored.
 */
class PdfController extends Controller
{
    public function __construct(protected DocumentGenerator $generator) {}

    /** Inline PDF for the live preview pane. */
    public function preview(GeneratePdfRequest $request): Response
    {
        return $this->generator->stream($request->templateKey(), $request->documentData());
    }

    /** The same PDF, as a download. */
    public function download(GeneratePdfRequest $request): Response
    {
        return $this->generator->download($request->templateKey(), $request->documentData());
    }
}
