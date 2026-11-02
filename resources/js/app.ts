import { createInertiaApp } from '@inertiajs/vue3';
import '../css/app.css';
import { initializeTheme } from './composables/useAppearance';
import { configureEcho } from '@laravel/echo-vue';

configureEcho({
    broadcaster: 'reverb',
});

const appName = import.meta.env.VITE_APP_NAME || 'Inertia Kitchen Sink';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    defaults: {
        visitOptions: (href, options) => ({
            preserveScroll: options?.preserveScroll ?? 'errors',
            ...options,
        }),
    },
});

// This will set light / dark mode on page load...
initializeTheme();
