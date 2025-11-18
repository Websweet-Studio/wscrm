import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/vue3';
import axios from 'axios';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';

// Function to initialize favicon from branding settings
function initializeFavicon() {
    // Check if we have branding settings with favicon
    if ((window as any).brandingSettings) {
        const faviconUrl = (window as any).brandingSettings.app_favicon;

        if (faviconUrl) {
            // Update or create favicon link elements
            const updateFavicon = (href: string, rel: string, type?: string) => {
                let link = document.querySelector(`link[rel="${rel}"]`) as HTMLLinkElement;
                if (!link) {
                    link = document.createElement('link');
                    link.rel = rel;
                    document.head.appendChild(link);
                }
                link.href = href;
                if (type) {
                    link.type = type;
                }
            };

            // Update all favicon variants
            updateFavicon(faviconUrl, 'icon', 'image/x-icon');
            updateFavicon(faviconUrl, 'icon', 'image/png');
            updateFavicon(faviconUrl, 'shortcut icon');
            updateFavicon(faviconUrl, 'apple-touch-icon');
        }
    }
}

// Setup CSRF token for axios
const token = document.head.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Setup axios interceptor for 419 errors
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 419) {
            console.warn('CSRF token expired. Reloading page...');
            window.location.reload();
        }
        return Promise.reject(error);
    },
);

// Setup Inertia router interceptor for 419 errors
router.on('error', (event) => {
    if (event.detail.response?.status === 419) {
        console.warn('CSRF token expired in Inertia request. Reloading page...');
        window.location.reload();
    }
});

createInertiaApp({
    title: (title) => {
        // Get app name from branding settings or use default
        const brandingAppName = (window as any).brandingSettings?.app_name;
        const appName = brandingAppName || import.meta.env.VITE_APP_NAME || 'Laravel';
        return title ? `${title} - ${appName}` : appName;
    },
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
    resolveErrors: (errors) => {
        // Handle CSRF token mismatch (419 errors)
        if (errors?.response?.status === 419 || errors?.status === 419) {
            console.warn('CSRF token mismatch detected. Reloading page...');
            window.location.reload();
            return {};
        }
        return errors;
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// Initialize favicon from branding settings
initializeFavicon();
