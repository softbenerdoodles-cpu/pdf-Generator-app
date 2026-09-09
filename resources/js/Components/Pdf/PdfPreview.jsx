import { AlertCircle, FileText, Loader2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { renderPreview } from '../../lib/pdf';

/**
 * Live preview of the draft.
 *
 * Edits are debounced and each render supersedes the one before it, so a fast
 * typist gets one request at the end rather than one per keystroke.
 */
export default function PdfPreview({ template, data, onErrors, delay = 700, idle = false }) {
    const [url, setUrl] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const previousUrl = useRef(null);

    useEffect(() => {
        // An untouched document has no required values yet; asking for it would
        // only produce a validation error the user has not earned.
        if (idle) {
            setLoading(false);

            return;
        }

        const controller = new AbortController();
        setLoading(true);

        const timer = setTimeout(async () => {
            try {
                const blob = await renderPreview(template, data, controller.signal);
                const next = URL.createObjectURL(blob);

                if (previousUrl.current) {
                    URL.revokeObjectURL(previousUrl.current);
                }

                previousUrl.current = next;
                setUrl(next);
                setError(null);
                onErrors?.({});
            } catch (failure) {
                if (failure.name === 'AbortError') {
                    return;
                }

                setError(failure.errors ?? {});
                onErrors?.(failure.errors ?? {});
            } finally {
                setLoading(false);
            }
        }, delay);

        return () => {
            clearTimeout(timer);
            controller.abort();
        };
        // onErrors is a stable callback from the parent; data drives the refresh.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [template, JSON.stringify(data), delay, idle]);

    useEffect(() => () => previousUrl.current && URL.revokeObjectURL(previousUrl.current), []);

    return (
        <div className="relative h-full overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-100 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            {/* The blob URL is used bare: a fragment on a blob: URL cannot be
                resolved against, and throws inside the viewer frame. */}
            {url ? (
                <iframe src={url} title="Document preview" className="h-full w-full" />
            ) : (
                <div className="grid h-full place-items-center p-8 text-center">
                    {idle ? (
                        <div className="max-w-xs">
                            <FileText className="mx-auto h-8 w-8 text-slate-300 dark:text-slate-600" />
                            <p className="mt-3 text-sm font-medium text-slate-600 dark:text-slate-400">
                                Your document appears here
                            </p>
                            <p className="mt-1 text-xs text-slate-500 dark:text-slate-500">
                                Start filling in the form, or load the example to see how it looks.
                            </p>
                        </div>
                    ) : error ? (
                        <div className="max-w-xs text-left">
                            <AlertCircle className="mx-auto h-8 w-8 text-amber-500" />
                            <p className="mt-3 text-center text-sm font-semibold text-slate-700 dark:text-slate-300">
                                A few details still needed
                            </p>
                            <ul className="mt-2 space-y-1">
                                {Object.values(error)
                                    .slice(0, 4)
                                    .map((messages) => (
                                        <li
                                            key={messages[0]}
                                            className="text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {messages[0]}
                                        </li>
                                    ))}
                            </ul>
                        </div>
                    ) : (
                        <Loader2 className="h-6 w-6 animate-spin text-slate-400" />
                    )}
                </div>
            )}

            {loading && url && (
                <div className="absolute right-3 top-3 flex items-center gap-2 rounded-full bg-white/90 px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm backdrop-blur dark:bg-slate-800/90 dark:text-slate-300">
                    <Loader2 className="h-3.5 w-3.5 animate-spin" />
                    Updating
                </div>
            )}
        </div>
    );
}
