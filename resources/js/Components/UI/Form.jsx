const CONTROL =
    'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm transition ' +
    'placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 ' +
    'dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-600';

/** A labelled input for the plain forms outside the document editor. */
export function Input({ label, error, as = 'input', id, ...props }) {
    const Tag = as;

    return (
        <div>
            <label htmlFor={id} className="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-slate-400">
                {label}
            </label>
            <Tag
                id={id}
                className={`${CONTROL} ${error ? 'border-rose-400 focus:border-rose-400 focus:ring-rose-500/20' : ''}`}
                {...props}
            />
            {error && <p className="mt-1 text-xs text-rose-600 dark:text-rose-400">{error}</p>}
        </div>
    );
}

export function Card({ title, description, children, footer }) {
    return (
        <section className="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            {title && (
                <header className="mb-5">
                    <h2 className="text-base font-bold text-slate-900 dark:text-white">{title}</h2>
                    {description && (
                        <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">{description}</p>
                    )}
                </header>
            )}

            {children}

            {footer && <div className="mt-5 flex justify-end gap-2">{footer}</div>}
        </section>
    );
}
