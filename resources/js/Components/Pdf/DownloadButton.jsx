import { Download, Loader2 } from 'lucide-react';
import { useState } from 'react';
import { downloadPdf } from '../../lib/pdf';

export default function DownloadButton({ template, data, filename, onErrors, children = 'Download PDF' }) {
    const [busy, setBusy] = useState(false);

    const click = async () => {
        setBusy(true);

        try {
            await downloadPdf(template, data, filename);
            onErrors?.({});
        } catch (failure) {
            onErrors?.(failure.errors ?? {});
        } finally {
            setBusy(false);
        }
    };

    return (
        <button
            type="button"
            onClick={click}
            disabled={busy}
            className="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-600/25 transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
        >
            {busy ? <Loader2 className="h-4 w-4 animate-spin" /> : <Download className="h-4 w-4" />}
            {children}
        </button>
    );
}
