import { Head, Link } from '@inertiajs/react';

const QUESTIONS = [
    {
        q: 'Do I need an account?',
        a: 'No. Creating, previewing and downloading a document all work without signing in, and there is no cap on how many you make.',
    },
    {
        q: 'Are my documents stored?',
        a: 'Not when you are signed out. The PDF is built in memory while your request is being handled and sent straight back to you — nothing is written to disk. Signing in is what adds somewhere to keep a copy.',
    },
    {
        q: 'How does the prompt box work?',
        a: 'It reads your sentence with a set of rules — it looks for a document type, a name, amounts, quantities and dates. There is no AI model behind it, which is why it answers instantly and why your text never leaves the app. Anything it cannot work out is left for you to fill in.',
    },
    {
        q: 'Why is the preview not updating?',
        a: 'The preview waits until a required field has a value. Fields that need attention are outlined in red with a note underneath.',
    },
    {
        q: 'Can I change the currency or add tax?',
        a: 'Yes. Invoices, quotes and estimates carry a currency selector, an optional tax rate, and a flat discount. Totals are recalculated as you type.',
    },
];

export default function Help() {
    return (
        <>
            <Head title="Help" />

            <div className="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
                <h1 className="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Help</h1>
                <p className="mt-3 text-slate-600 dark:text-slate-400">
                    Short answers to the things people ask most.
                </p>

                <dl className="mt-8 space-y-4">
                    {QUESTIONS.map(({ q, a }) => (
                        <div
                            key={q}
                            className="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <dt className="text-sm font-bold text-slate-900 dark:text-white">{q}</dt>
                            <dd className="mt-1.5 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{a}</dd>
                        </div>
                    ))}
                </dl>

                <div className="mt-8 rounded-2xl bg-indigo-50 p-6 text-center dark:bg-indigo-500/10">
                    <p className="text-sm font-semibold text-indigo-900 dark:text-indigo-200">
                        Ready to make one?
                    </p>
                    <Link
                        href="/templates"
                        className="mt-3 inline-flex rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                    >
                        Browse document types
                    </Link>
                </div>
            </div>
        </>
    );
}
