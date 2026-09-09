import { Head, Link, router } from '@inertiajs/react';
import { Download, FileText, Trash2 } from 'lucide-react';
import Button from '../../Components/UI/Button';
import { accentClasses } from '../../lib/accents';
import { iconFor } from '../../lib/icons';

function formatSize(bytes) {
    return `${(bytes / 1024).toFixed(0)} KB`;
}

export default function Index({ documents }) {
    const remove = (id) => {
        if (confirm('Delete this document? The PDF will be removed too.')) {
            router.delete(`/documents/${id}`, { preserveScroll: true });
        }
    };

    return (
        <>
            <Head title="My documents" />

            <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
                <header className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            My documents
                        </h1>
                        <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Everything you have chosen to keep.
                        </p>
                    </div>

                    <Button href="/templates">New document</Button>
                </header>

                {documents.data.length === 0 ? (
                    <div className="mt-10 rounded-2xl border border-dashed border-slate-300 p-12 text-center dark:border-slate-700">
                        <FileText className="mx-auto h-9 w-9 text-slate-300 dark:text-slate-600" />
                        <p className="mt-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                            Nothing saved yet
                        </p>
                        <p className="mx-auto mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">
                            Make a document and choose “Save to my documents” — you can still download without
                            keeping a copy.
                        </p>
                        <Button href="/templates" className="mt-5">
                            Create a document
                        </Button>
                    </div>
                ) : (
                    <ul className="mt-8 space-y-3">
                        {documents.data.map((document) => {
                            const Icon = iconFor(document.icon);

                            return (
                                <li
                                    key={document.id}
                                    className="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                                >
                                    <span
                                        className={`grid h-11 w-11 shrink-0 place-items-center rounded-xl ${accentClasses(document.accent)}`}
                                    >
                                        <Icon className="h-5 w-5" />
                                    </span>

                                    <div className="min-w-0 flex-1">
                                        <Link
                                            href={`/documents/${document.id}`}
                                            className="block truncate text-sm font-semibold text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400"
                                        >
                                            {document.title}
                                        </Link>
                                        <p className="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                            {document.type_name} · {document.created_at} · {formatSize(document.size)}
                                        </p>
                                    </div>

                                    <a
                                        href={`/documents/${document.id}/download`}
                                        className="grid h-9 w-9 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                        aria-label={`Download ${document.title}`}
                                    >
                                        <Download className="h-4 w-4" />
                                    </a>

                                    <button
                                        type="button"
                                        onClick={() => remove(document.id)}
                                        aria-label={`Delete ${document.title}`}
                                        className="grid h-9 w-9 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </button>
                                </li>
                            );
                        })}
                    </ul>
                )}

                {documents.links?.length > 3 && (
                    <nav className="mt-8 flex flex-wrap justify-center gap-1">
                        {documents.links.map((link) => (
                            <Link
                                key={link.label}
                                href={link.url ?? '#'}
                                disabled={!link.url}
                                className={`rounded-lg px-3 py-1.5 text-sm ${
                                    link.active
                                        ? 'bg-indigo-600 text-white'
                                        : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800'
                                } ${link.url ? '' : 'pointer-events-none opacity-40'}`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </nav>
                )}
            </div>
        </>
    );
}
