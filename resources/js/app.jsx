import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import AppLayout from './Layouts/AppLayout';

const pages = import.meta.glob('./Pages/**/*.jsx');

createInertiaApp({
    title: (title) => (title ? `${title} · Aneta Prime` : 'Aneta Prime'),

    resolve: async (name) => {
        const page = (await pages[`./Pages/${name}.jsx`]()).default;

        // Every page gets the shell unless it opts out with `page.layout = null`.
        page.layout ??= (content) => <AppLayout>{content}</AppLayout>;

        return page;
    },

    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },

    progress: { color: '#4f46e5' },
});
