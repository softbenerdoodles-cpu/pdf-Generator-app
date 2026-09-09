/**
 * The two tilted document cards flanking the hero.
 *
 * Decorative only — they are hidden from assistive tech and from small
 * screens, where the headline needs the whole width.
 */
function Line({ w = 'w-full', tone = 'bg-slate-200 dark:bg-slate-700' }) {
    return <div className={`h-1.5 rounded-full ${w} ${tone}`} />;
}

function Card({ kind, className }) {
    const invoice = kind === 'invoice';
    const accent = invoice ? 'text-slate-400' : 'text-indigo-500';

    return (
        <div
            aria-hidden="true"
            className={`w-64 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900 ${className}`}
        >
            <div className="flex items-start justify-between">
                <div className="space-y-1.5">
                    <div className="h-6 w-6 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600" />
                    <Line w="w-20" />
                    <Line w="w-16" />
                </div>
                <div className={`text-sm font-extrabold uppercase tracking-wider ${accent}`}>
                    {invoice ? 'Invoice' : 'Quote'}
                </div>
            </div>

            <div className="mt-5 space-y-2">
                <Line w="w-24" tone="bg-slate-300 dark:bg-slate-600" />
                <Line w="w-32" />
                <Line w="w-28" />
            </div>

            <div className="mt-5 space-y-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                {[0, 1, 2].map((row) => (
                    <div key={row} className="flex items-center gap-3">
                        <Line w="flex-1" />
                        <Line w="w-8" />
                        <Line w="w-10" tone="bg-slate-300 dark:bg-slate-600" />
                    </div>
                ))}
            </div>

            <div className="mt-4 flex items-center justify-between border-t border-slate-200 pt-3 dark:border-slate-700">
                <Line w="w-10" />
                <div className="h-2.5 w-16 rounded-full bg-slate-800 dark:bg-slate-300" />
            </div>
        </div>
    );
}

export default function HeroCards() {
    return (
        <>
            <div className="pointer-events-none absolute -left-16 top-10 hidden lg:block xl:left-0">
                <Card kind="invoice" className="card-tilt-left" />
            </div>
            <div className="pointer-events-none absolute -right-16 top-4 hidden lg:block xl:right-0">
                <Card kind="quote" className="card-tilt-right" />
            </div>
        </>
    );
}
