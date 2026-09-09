<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneratePdfRequest;
use App\Models\Document;
use App\Services\Document\DocumentGenerator;
use App\Services\Templates\TemplateManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Documents a signed-in user chose to keep.
 *
 * Guests never reach these routes; for them a document lives only as long as
 * the request that renders it.
 */
class DocumentController extends Controller
{
    public function __construct(
        protected DocumentGenerator $generator,
        protected TemplateManager $templates,
    ) {}

    public function index(Request $request): Response
    {
        $documents = $request->user()->documents()->paginate(20)->through(fn (Document $document) => [
            'id' => $document->id,
            'title' => $document->title,
            'template' => $document->template,
            'type_name' => $this->templates->get($document->template)->name(),
            'accent' => $this->templates->get($document->template)->accent(),
            'icon' => $this->templates->get($document->template)->icon(),
            'size' => $document->size,
            'created_at' => $document->created_at->toDayDateTimeString(),
        ]);

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'types' => $this->templates->summaries(),
        ]);
    }

    /** Render the draft and keep a copy. */
    public function store(GeneratePdfRequest $request): RedirectResponse
    {
        $document = $this->generator->store(
            $request->user()->id,
            $request->templateKey(),
            $request->documentData(),
        );

        return to_route('documents.show', $document)->with('status', 'Saved to your documents.');
    }

    public function show(Request $request, Document $document): Response
    {
        $this->authorizeDocument($request, $document);

        return Inertia::render('Documents/Show', [
            'document' => [
                'id' => $document->id,
                'title' => $document->title,
                'template' => $document->templateInstance()->summary(),
                'data' => $document->data,
                'size' => $document->size,
                'created_at' => $document->created_at->toDayDateTimeString(),
                'exists' => $document->fileExists(),
            ],
            'types' => $this->templates->summaries(),
        ]);
    }

    public function download(Request $request, Document $document): StreamedResponse
    {
        $this->authorizeDocument($request, $document);

        abort_unless($document->fileExists(), 404, 'That file is no longer on disk.');

        return Storage::disk(config('pdf.disk'))->download($document->path, $document->filename());
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        $document->delete();

        return to_route('documents.index')->with('status', 'Document deleted.');
    }

    /** A document is only ever visible to the account that saved it. */
    protected function authorizeDocument(Request $request, Document $document): void
    {
        abort_unless($document->user_id === $request->user()->id, 404);
    }
}
