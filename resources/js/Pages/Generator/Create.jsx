import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Loader2, Maximize2, Save, UserRound, Wand2 } from 'lucide-react';
import { useCallback, useMemo, useState } from 'react';
import { editorFor } from '../../Components/Documents';
import SmartResult from '../../Components/Generator/SmartResult';
import DownloadButton from '../../Components/Pdf/DownloadButton';
import PdfPreview from '../../Components/Pdf/PdfPreview';
import { accentClasses } from '../../lib/accents';
import { iconFor } from '../../lib/icons';
import { setIn } from '../../lib/objects';

export default function Create({ template, sample, business, customers = [] }) {
    const { flash, auth } = usePage().props;
    const parsed = flash?.parsed?.template === template.key ? flash.parsed : null;

    const [data, setData] = useState(() => {
        if (parsed?.data) {
            return parsed.data;
        }

        // A signed-in user's own details belong on the document already.
        return business && 'from' in template.blank
            ? { ...template.blank, from: { ...template.blank.from, ...business } }
            : template.blank;
    });
    // Field-level errors are only painted once the user asks for the document.
    // What the preview reports goes to the preview panel instead, so a draft
    // does not open covered in red.
    const [errors, setErrors] = useState({});

    // Until something has been entered there is nothing worth rendering.
    const [touched, setTouched] = useState(Boolean(parsed));

    const change = useCallback((path, value) => {
        setTouched(true);
        setData((current) => setIn(current, path, value));
    }, []);

    const Editor = useMemo(() => editorFor(template.key), [template.key]);
    const Icon = iconFor(template.icon);
    const filename = `${template.key}.pdf`;

    const loadExample = () => {
        setErrors({});
        setTouched(true);
        setData(sample);
    };

    const openFullPreview = () => {
        router.post('/generate/preview', { template: template.key, data });
    };

    const [saving, setSaving] = useState(false);

    const save = () => {
        setSaving(true);
        router.post(
            '/documents',
            { template: template.key, data },
            {
                onError: setErrors,
                onFinish: () => setSaving(false),
            },
        );
    };

    // Saved customers drop straight into the recipient block.
    const useCustomer = (index) => {
        const customer = customers[index];

        if (!customer) {
            return;
        }

        setTouched(true);
        setData((current) => ({ ...current, to: { ...current.to, ...customer } }));
    };

    return (
        <>
            <Head title={template.name} />

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <Link
                    href="/templates"
                    className="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200"
                >
                    <ArrowLeft className="h-4 w-4" />
                    All document types
                </Link>

                <header className="mt-4 flex flex-wrap items-center gap-4">
                    <span className={`grid h-12 w-12 place-items-center rounded-2xl ${accentClasses(template.accent)}`}>
                        <Icon className="h-6 w-6" />
                    </span>

                    <div className="min-w-0 flex-1">
                        <h1 className="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            {template.name}
                        </h1>
                        <p className="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{template.description}</p>
                    </div>

                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={loadExample}
                            className="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800"
                        >
                            <Wand2 className="h-4 w-4" />
                            Load example
                        </button>

                        {auth?.user && (
                            <button
                                type="button"
                                onClick={save}
                                disabled={saving}
                                className="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 disabled:opacity-60 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800"
                            >
                                {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
                                Save
                            </button>
                        )}

                        <DownloadButton
                            template={template.key}
                            data={data}
                            filename={filename}
                            onErrors={setErrors}
                        />
                    </div>
                </header>

                {parsed && (
                    <div className="mt-6">
                        <SmartResult parsed={parsed} />
                    </div>
                )}

                <div className="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,26rem)] xl:grid-cols-[minmax(0,1fr)_minmax(0,32rem)]">
                    <div className="space-y-4">
                        {customers.length > 0 && 'to' in template.blank && (
                            <label className="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <UserRound className="h-4 w-4 text-slate-400" />
                                <span className="font-semibold text-slate-700 dark:text-slate-200">
                                    Use a saved customer
                                </span>
                                <select
                                    defaultValue=""
                                    onChange={(event) => useCustomer(Number(event.target.value))}
                                    className="ml-auto rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <option value="" disabled>
                                        Choose…
                                    </option>
                                    {customers.map((customer, index) => (
                                        <option key={customer.name + index} value={index}>
                                            {customer.name}
                                        </option>
                                    ))}
                                </select>
                            </label>
                        )}

                        <Editor
                            fields={template.fields}
                            data={data}
                            onChange={change}
                            errors={errors}
                        />
                    </div>

                    <div className="lg:sticky lg:top-24 lg:h-[calc(100vh-8rem)]">
                        <div className="mb-2 flex items-center justify-between">
                            <span className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                Live preview
                            </span>
                            <button
                                type="button"
                                onClick={openFullPreview}
                                className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 transition hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400"
                            >
                                <Maximize2 className="h-3.5 w-3.5" />
                                Full screen
                            </button>
                        </div>

                        <div className="h-[32rem] lg:h-[calc(100%-1.75rem)]">
                            <PdfPreview template={template.key} data={data} idle={!touched} />
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
