import { Link } from '@inertiajs/react';

const VARIANTS = {
    primary:
        'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20 hover:bg-indigo-700 focus-visible:outline-indigo-600',
    secondary:
        'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 focus-visible:outline-slate-400 ' +
        'dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800',
    ghost:
        'text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-slate-400 ' +
        'dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100',
    danger:
        'text-rose-600 hover:bg-rose-50 focus-visible:outline-rose-500 dark:text-rose-400 dark:hover:bg-rose-500/10',
};

const SIZES = {
    sm: 'px-3 py-1.5 text-sm gap-1.5',
    md: 'px-4 py-2.5 text-sm gap-2',
    lg: 'px-6 py-3.5 text-base gap-2.5',
};

export default function Button({
    variant = 'primary',
    size = 'md',
    href,
    className = '',
    children,
    ...props
}) {
    const classes = [
        'inline-flex items-center justify-center rounded-xl font-semibold transition',
        'focus-visible:outline-2 focus-visible:outline-offset-2',
        'disabled:cursor-not-allowed disabled:opacity-50',
        VARIANTS[variant],
        SIZES[size],
        className,
    ].join(' ');

    if (href) {
        return (
            <Link href={href} className={classes} {...props}>
                {children}
            </Link>
        );
    }

    return (
        <button className={classes} {...props}>
            {children}
        </button>
    );
}
