import { createInertiaApp } from '@inertiajs/vue3';
import { echo } from '@inertiajs/vue3/echo';
import '../css/app.css';
import LayerLoading from './components/LayerLoading.vue';
import LayerShell from './components/LayerShell.vue';
import { initializeTheme } from './composables/useAppearance';
import { configureEcho } from '@laravel/echo-vue';

configureEcho({
    broadcaster: 'reverb',
});

const appName = import.meta.env.VITE_APP_NAME || 'Inertia Kitchen Sink';

void createInertiaApp({
    layer: LayerShell,
    loading: LayerLoading,
    live: echo(),
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
