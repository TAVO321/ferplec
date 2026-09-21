import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        host: '127.0.0.1',
        port: 5173,
    },
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        rolldownOptions: {
            output: {
                codeSplitting: {
                    groups: [
                        { name: 'vue', test: /node_modules\/(vue|@vue)\// },
                        { name: 'inertia', test: /node_modules\/@inertiajs\// },
                        { name: 'ziggy', test: /(vendor\/tightenco\/ziggy|node_modules\/ziggy-js)\// },
                    ],
                },
            },
        },
    },
});