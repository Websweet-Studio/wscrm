import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig(({ command }) => {
    const plugins = [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ];

    // Only add wayfinder in development mode to avoid database dependency during build
    if (command === 'serve') {
        plugins.splice(2, 0, wayfinder({
            formVariants: true,
        }));
    }

    return {
        plugins,
        build: {
            emptyOutDir: true,
        },
        // No need for special resolves in build mode
        resolve: {}
    };
});
