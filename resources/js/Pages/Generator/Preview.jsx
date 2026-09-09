import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import DownloadButton from '../../Components/Pdf/DownloadButton';
import PdfPreview from '../../Components/Pdf/PdfPreview';

/** A full-width read of the finished document before downloading it. */
export default function Preview({ template, data, title, filename }) {
    return (
        <>
            <Head title={title} />

            <div className="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="flex flex-wrap items-center gap-4">
                    <Link
                        href={`/generate/${template.key}`}
                        className="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Back to editor
                    </Link>

                    <h1 className="min-w-0 flex-1 truncate text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        {title}
                    </h1>

                    <DownloadButton template={template.key} data={data} filename={filename} />
                </div>

                <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Going back to the editor starts a fresh draft — nothing here is stored on our side.
                </p>

                <div className="mt-6 h-[80vh]">
                    <PdfPreview template={template.key} data={data} delay={0} />
                </div>
            </div>
        </>
    );
}
