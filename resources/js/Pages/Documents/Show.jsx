import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Download, Trash2 } from 'lucide-react';
import PdfPreview from '../../Components/Pdf/PdfPreview';
import { accentClasses } from '../../lib/accents';
import { iconFor } from '../../lib/icons';

export default function Show({ document }) {
    const Icon = iconFor(document.template.icon);

    const remove = () => {
        if (confirm('Delete this document? The PDF will be removed too.')) {
            router.delete(`/documents/${document.id}`);
        }
    };

    return (
        <>
            <Head title={document.title} />

            <div className="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                <Link
                    href="/documents"
                    className="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200"
                >
                    <ArrowLeft className="h-4 w-4" />
                    My documents
                </Link>

                <header className="mt-4 flex flex-wrap items-center gap-4">
                    <span
                        className={`grid h-12 w-12 place-items-center rounded-2xl ${accentClasses(document.template.accent)}`}
                    >
                        <Icon className="h-6 w-6" />
                    </span>

                    <div className="min-w-0 flex-1">
                        <h1 className="truncate text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            {document.title}
                        </h1>
                        <p className="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                            {document.template.name} · saved {document.created_at}
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <Link
                            href={`/generate/${document.template.key}`}
                            className="rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800"
                        >
                            New {document.template.name.toLowerCase()}
                        </Link>

                        <a
                            href={`/documents/${document.id}/download`}
                            className="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-600/25 transition hover:bg-indigo-700"
                        >
                            <Download className="h-4 w-4" />
                            Download
                        </a>

                        <button
                            type="button"
                            onClick={remove}
                            aria-label="Delete document"
                            className="grid h-10 w-10 place-items-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10"
                        >
                            <Trash2 className="h-4 w-4" />
                        </button>
                    </div>
                </header>

                <div className="mt-6 h-[75vh]">
                    <PdfPreview template={document.template.key} data={document.data} delay={0} />
                </div>
            </div>
        </>
    );
}
