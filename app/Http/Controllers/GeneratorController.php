<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneratePdfRequest;
use App\Http\Requests\ParseDocumentRequest;
use App\Services\Document\DocumentGenerator;
use App\Services\Document\DocumentParser;
use App\Services\Templates\TemplateManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The pages people move through: pick a type or describe what you want,
 * edit the draft, then review it.
 */
class GeneratorController extends Controller
{
    public function __construct(
        protected TemplateManager $templates,
        protected DocumentGenerator $generator,
    ) {}

    public function home(): Response
    {
        return Inertia::render('Home', [
            'types' => $this->templates->summaries(),
            'examples' => [
                'Create an invoice for John Smith for $850 for logo design, due September 30',
                'Quote for Bright Ideas Co. — website design $1,200 and SEO setup $300, valid until October 15',
                'Receipt for Acme Ltd for $450 paid by bank transfer',
                'Certificate of completion for Jordan Ellis',
            ],
        ]);
    }

    /** The full type gallery. */
    public function index(): Response
    {
        return Inertia::render('Generator/Index', [
            'types' => $this->templates->summaries(),
        ]);
    }

    /**
     * Read a prompt and drop the user into the editor with a draft ready.
     */
    public function parse(ParseDocumentRequest $request, DocumentParser $parser): RedirectResponse
    {
        $parsed = $parser->parse(
            $request->string('prompt')->value(),
            $request->input('template'),
        );

        return to_route('generator.create', $parsed->template)
            ->with('parsed', $parsed->toArray());
    }

    /** The editor for one document type. */
    public function create(Request $request, string $template): Response
    {
        $documentTemplate = $this->templates->get($template);
        $user = $request->user();

        return Inertia::render('Generator/Create', [
            'template' => $documentTemplate->toArray(),
            'types' => $this->templates->summaries(),
            'sample' => $documentTemplate->sample(),
            // Signed-in extras. Guests get null and type their own details.
            'business' => $user?->businessProfile?->toParty(),
            'customers' => $user
                ? $user->customers->map(fn ($customer) => $customer->toParty())->values()
                : [],
        ]);
    }

    /** A full-page review of the draft before downloading it. */
    public function preview(GeneratePdfRequest $request): Response
    {
        ['template' => $template, 'data' => $data] = $this->generator->prepare(
            $request->templateKey(),
            $request->documentData(),
        );

        return Inertia::render('Generator/Preview', [
            'template' => $template->summary(),
            'data' => $data,
            'title' => $this->generator->title($template, $data),
            'filename' => $this->generator->filename($template, $data),
        ]);
    }
}
