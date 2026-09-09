/**
 * Tile colours per document type.
 *
 * Written out in full because Tailwind only ships classes it can see in the
 * source — a template literal like `bg-${accent}-50` would be purged away.
 */
const ACCENTS = {
    blue: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
    green: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
    purple: 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400',
    orange: 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400',
    pink: 'bg-pink-50 text-pink-600 dark:bg-pink-500/10 dark:text-pink-400',
    sky: 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
    indigo: 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
    amber: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
};

export function accentClasses(accent) {
    return ACCENTS[accent] ?? ACCENTS.blue;
}
