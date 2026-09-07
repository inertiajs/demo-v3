import { createInertiaApp } from '@inertiajs/vue3';
import '../css/app.css';
import LayerLoading from './components/LayerLoading.vue';
import LayerShell from './components/LayerShell.vue';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Inertia Kitchen Sink';

createInertiaApp({
    layer: LayerShell,
    loading: LayerLoading,
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
