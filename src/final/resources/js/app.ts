import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import { i18n } from '@/i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => (name === 'Welcome' ? null : AppLayout),
    progress: {
        color: '#4B5563',
    },
    withApp: (app) => {
        app.use(i18n);
    },
});

if (typeof document !== 'undefined') {
    document.documentElement.setAttribute('lang', i18n.global.locale.value);
}

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
