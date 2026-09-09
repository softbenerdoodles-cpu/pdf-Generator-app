import { useForm } from '@inertiajs/react';
import { ArrowRight, Loader2, Sparkles } from 'lucide-react';

/**
 * The one-line brief at the top of the home page.
 *
 * The prompt is read on the server by a set of rules — no model, no API call —
 * so the round trip is fast and the text never leaves the app.
 */
export default function PromptBox({ examples = [], template = null }) {
    const { data, setData, post, processing, errors } = useForm({
        prompt: '',
        template,
    });

    const submit = (event) => {
        event.preventDefault();
        post('/generate/parse', { preserveScroll: true });
    };

    return (
        <div className="mx-auto w-full max-w-3xl">
            <form
                onSubmit={submit}
                className="flex flex-col gap-3 rounded-2xl border border-slate-200/80 bg-white p-3 shadow-xl shadow-slate-900/5 sm:flex-row sm:items-center dark:border-slate-800 dark:bg-slate-900"
            >
                <Sparkles className="ml-2 hidden h-5 w-5 shrink-0 text-indigo-500 sm:block" />

                <input
                    type="text"
                    value={data.prompt}
                    onChange={(event) => setData('prompt', event.target.value)}
                    placeholder="Create an invoice for John Smith for $850 for logo design, due September 30"
                    aria-label="Describe the document you need"
                    className="min-w-0 flex-1 bg-transparent px-2 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none dark:text-slate-200 dark:placeholder:text-slate-500"
                />

                <button
                    type="submit"
                    disabled={processing}
                    className="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-indigo-600/25 transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {processing ? (
                        <Loader2 className="h-4 w-4 animate-spin" />
                    ) : (
                        <ArrowRight className="h-4 w-4" />
                    )}
                    Generate Document
                </button>
            </form>

            {errors.prompt && (
                <p className="mt-2 text-center text-sm text-rose-600 dark:text-rose-400">{errors.prompt}</p>
            )}

            {examples.length > 0 && (
                <div className="mt-4 flex flex-wrap items-center justify-center gap-2">
                    <span className="text-xs font-medium text-slate-400 dark:text-slate-500">Try:</span>
                    {examples.slice(0, 2).map((example) => (
                        <button
                            key={example}
                            type="button"
                            onClick={() => setData('prompt', example)}
                            className="max-w-full truncate rounded-full border border-slate-200 bg-white px-3 py-1 text-xs text-slate-500 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-indigo-500/50 dark:hover:text-indigo-300"
                        >
                            {example}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
