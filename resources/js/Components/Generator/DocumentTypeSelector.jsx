import { Link } from '@inertiajs/react';
import { LayoutGrid } from 'lucide-react';
import { accentClasses } from '../../lib/accents';
import { iconFor } from '../../lib/icons';

/** The grid of document types. `limit` trims it and adds a "View all" tile. */
export default function DocumentTypeSelector({ types, limit = null, showViewAll = false }) {
    const shown = limit ? types.slice(0, limit) : types;
    const withViewAll = showViewAll || (limit !== null && types.length > limit);
    const columns = withViewAll ? 'xl:grid-cols-9' : 'xl:grid-cols-8';

    return (
        <div className={`grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 ${columns}`}>
            {shown.map((type) => {
                const Icon = iconFor(type.icon);

                return (
                    <Link
                        key={type.key}
                        href={`/generate/${type.key}`}
                        title={type.description}
                        className="group flex flex-col items-center gap-3 rounded-2xl border border-slate-200/80 bg-white p-5 text-center shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-500/40"
                    >
                        <span className={`grid h-12 w-12 place-items-center rounded-2xl ${accentClasses(type.accent)}`}>
                            <Icon className="h-6 w-6" />
                        </span>
                        <span className="text-sm font-semibold text-slate-800 dark:text-slate-100">{type.name}</span>
                    </Link>
                );
            })}

            {withViewAll && (
                <Link
                    href="/templates"
                    className="group flex flex-col items-center gap-3 rounded-2xl border border-slate-200/80 bg-white p-5 text-center shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-500/40"
                >
                    <span className="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <LayoutGrid className="h-6 w-6" />
                    </span>
                    <span className="text-sm font-semibold text-slate-800 dark:text-slate-100">View All</span>
                </Link>
            )}
        </div>
    );
}
