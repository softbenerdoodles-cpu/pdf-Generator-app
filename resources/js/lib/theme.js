/**
 * Light/dark preference.
 *
 * The class is applied by an inline script in the root Blade view before first
 * paint; this module only handles changes made after the app has booted.
 */
const STORAGE_KEY = 'theme';

export function currentTheme() {
    if (typeof document === 'undefined') {
        return 'light';
    }

    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

export function applyTheme(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');

    try {
        localStorage.setItem(STORAGE_KEY, theme);
    } catch {
        // A browser with storage blocked still gets the toggle for this visit.
    }
}
