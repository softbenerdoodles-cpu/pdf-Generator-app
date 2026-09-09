import { Sparkles, X } from 'lucide-react';
import { useState } from 'react';

/**
 * What the parser took from the prompt.
 *
 * Being explicit about this matters: the rules are simple, so the user needs
 * to see what was understood before trusting the draft.
 */
export default function SmartResult({ parsed }) {
    const [dismissed, setDismissed] = useState(false);

    if (!parsed || dismissed) {
        return null;
    }

    const percent = Math.round((parsed.confidence ?? 0) * 100);

    return (
        <div className="relative rounded-2xl border border-indigo-200 bg-indigo-50/70 p-4 dark:border-indigo-500/30 dark:bg-indigo-500/10">
            <button
                type="button"
                onClick={() => setDismissed(true)}
                aria-label="Dismiss"
                className="absolute right-3 top-3 rounded-lg p-1 text-indigo-400 transition hover:bg-indigo-100 hover:text-indigo-700 dark:hover:bg-indigo-500/20"
            >
                <X className="h-4 w-4" />
            </button>

            <div className="flex items-start gap-3">
                <Sparkles className="mt-0.5 h-5 w-5 shrink-0 text-indigo-500" />
                <div className="min-w-0">
                    <h2 className="text-sm font-bold text-indigo-900 dark:text-indigo-200">
                        Here is what we picked up
                    </h2>

                    <ul className="mt-2 space-y-1">
                        {(parsed.understood ?? []).map((note) => (
                            <li key={note} className="text-sm text-indigo-800/90 dark:text-indigo-200/80">
                                {note}
                            </li>
                        ))}
                    </ul>

                    <p className="mt-2 text-xs text-indigo-700/70 dark:text-indigo-300/60">
                        {percent >= 75
                            ? 'Check the details below, then download.'
                            : 'Some fields were left blank — fill them in below.'}
                    </p>
                </div>
            </div>
        </div>
    );
}
