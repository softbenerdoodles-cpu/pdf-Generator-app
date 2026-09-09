import { Link, usePage } from '@inertiajs/react';
import { ChevronDown, FolderOpen, LogOut, Moon, Settings, Sun } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { applyTheme, currentTheme } from '../lib/theme';
import { accentClasses } from '../lib/accents';
import { iconFor } from '../lib/icons';

/** The eight types are the same everywhere, so the nav reads them from a page
 *  prop when there is one and falls back to a short list otherwise. */
const FALLBACK_TYPES = [
    { key: 'invoice', name: 'Invoice', icon: 'file-text', accent: 'blue' },
    { key: 'quote', name: 'Quote', icon: 'file-check', accent: 'green' },
    { key: 'receipt', name: 'Receipt', icon: 'receipt', accent: 'purple' },
    { key: 'certificate', name: 'Certificate', icon: 'award', accent: 'amber' },
];

function Logo() {
    return (
        <Link href="/" className="flex items-center gap-2.5">
            <span className="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 shadow-sm shadow-indigo-600/30">
                <svg viewBox="0 0 24 24" className="h-5 w-5 text-white" aria-hidden="true">
                    <path d="M12 4 4.5 19h3.2l1.5-3.2h5.6l1.5 3.2h3.2L12 4Zm-1.6 9.2L12 9.6l1.6 3.6h-3.2Z" fill="currentColor" />
                </svg>
            </span>
            <span className="text-lg font-extrabold tracking-tight">
                Aneta <span className="text-indigo-600 dark:text-indigo-400">Prime</span>
            </span>
        </Link>
    );
}

function DocumentsMenu({ types }) {
    const [open, setOpen] = useState(false);
    const container = useRef(null);

    useEffect(() => {
        if (!open) {
            return;
        }

        const close = (event) => {
            if (!container.current?.contains(event.target)) {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', close);
        document.addEventListener('keydown', (e) => e.key === 'Escape' && setOpen(false));

        return () => document.removeEventListener('mousedown', close);
    }, [open]);

    return (
        <div ref={container} className="relative">
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                aria-expanded={open}
                className="flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
            >
                Documents
                <ChevronDown className={`h-4 w-4 transition ${open ? 'rotate-180' : ''}`} />
            </button>

            {open && (
                <div className="absolute left-0 top-full z-30 mt-1 w-64 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/5 dark:border-slate-700 dark:bg-slate-900">
                    {types.map((type) => {
                        const Icon = iconFor(type.icon);

                        return (
                            <Link
                                key={type.key}
                                href={`/generate/${type.key}`}
                                onClick={() => setOpen(false)}
                                className="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                            >
                                <span className={`grid h-7 w-7 place-items-center rounded-lg ${accentClasses(type.accent)}`}>
                                    <Icon className="h-4 w-4" />
                                </span>
                                {type.name}
                            </Link>
                        );
                    })}
                </div>
            )}
        </div>
    );
}

function ThemeToggle() {
    const [theme, setTheme] = useState('light');

    useEffect(() => setTheme(currentTheme()), []);

    const toggle = () => {
        const next = theme === 'dark' ? 'light' : 'dark';
        applyTheme(next);
        setTheme(next);
    };

    return (
        <button
            type="button"
            onClick={toggle}
            aria-label={theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'}
            className="grid h-9 w-9 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
        >
            {theme === 'dark' ? <Moon className="h-5 w-5" /> : <Sun className="h-5 w-5" />}
        </button>
    );
}

function AccountMenu({ user }) {
    const [open, setOpen] = useState(false);
    const container = useRef(null);

    useEffect(() => {
        if (!open) {
            return;
        }

        const close = (event) => {
            if (!container.current?.contains(event.target)) {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', close);

        return () => document.removeEventListener('mousedown', close);
    }, [open]);

    const initials = user.name
        .split(' ')
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();

    return (
        <div ref={container} className="relative">
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                aria-expanded={open}
                aria-label="Account menu"
                className="grid h-9 w-9 place-items-center rounded-full bg-indigo-600 text-xs font-bold text-white"
            >
                {initials}
            </button>

            {open && (
                <div className="absolute right-0 top-full z-30 mt-1 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                    <div className="px-3 py-2">
                        <div className="truncate text-sm font-semibold text-slate-900 dark:text-white">{user.name}</div>
                        <div className="truncate text-xs text-slate-500 dark:text-slate-400">{user.email}</div>
                    </div>

                    <div className="my-1 h-px bg-slate-100 dark:bg-slate-800" />

                    <Link
                        href="/documents"
                        onClick={() => setOpen(false)}
                        className="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        <FolderOpen className="h-4 w-4 text-slate-400" />
                        My documents
                    </Link>

                    <Link
                        href="/account"
                        onClick={() => setOpen(false)}
                        className="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        <Settings className="h-4 w-4 text-slate-400" />
                        Account
                    </Link>

                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        className="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        <LogOut className="h-4 w-4 text-slate-400" />
                        Sign out
                    </Link>
                </div>
            )}
        </div>
    );
}

function NavLink({ href, children }) {
    const { url } = usePage();
    const active = href === '/' ? url === '/' : url.startsWith(href);

    return (
        <Link
            href={href}
            className={`rounded-lg px-3 py-2 text-sm font-medium transition ${
                active
                    ? 'text-slate-900 dark:text-white'
                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100'
            }`}
        >
            {children}
        </Link>
    );
}

export default function AppLayout({ children }) {
    const { types, auth } = usePage().props;

    return (
        <div className="flex min-h-full flex-col">
            <header className="sticky top-0 z-20 border-b border-slate-200/80 bg-white/85 backdrop-blur dark:border-slate-800 dark:bg-slate-950/85">
                <div className="mx-auto flex h-16 max-w-7xl items-center gap-2 px-4 sm:px-6 lg:px-8">
                    <Logo />

                    <nav className="ml-6 hidden items-center gap-1 md:flex">
                        <NavLink href="/">Home</NavLink>
                        <DocumentsMenu types={types?.length ? types : FALLBACK_TYPES} />
                        <NavLink href="/templates">Templates</NavLink>
                        <NavLink href="/help">Help</NavLink>
                    </nav>

                    <div className="ml-auto flex items-center gap-2">
                        <Link
                            href="/generate/invoice"
                            className="hidden rounded-xl bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100 sm:inline-flex dark:bg-indigo-500/10 dark:text-indigo-300 dark:hover:bg-indigo-500/20"
                        >
                            Free PDF Generator
                        </Link>

                        <ThemeToggle />

                        {auth?.user ? (
                            <AccountMenu user={auth.user} />
                        ) : (
                            <Link
                                href="/login"
                                className="hidden rounded-xl px-3 py-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900 sm:inline-flex dark:text-slate-400 dark:hover:text-slate-100"
                            >
                                Sign in
                            </Link>
                        )}
                    </div>
                </div>
            </header>

            <main className="flex-1">{children}</main>

            <footer className="border-t border-slate-200 py-10 text-center dark:border-slate-800">
                <div className="text-[11px] font-bold uppercase tracking-[0.35em] text-slate-400 dark:text-slate-500">
                    Aneta Prime
                </div>
                <div className="mt-2 text-[11px] uppercase tracking-[0.18em] text-slate-400 dark:text-slate-600">
                    Simple tools for a more productive you
                </div>
            </footer>
        </div>
    );
}
