import '../css/app.css';
import '@fontsource/figtree/latin-400.css';
import '@fontsource/figtree/latin-500.css';
import '@fontsource/figtree/latin-600.css';
import '@fontsource/figtree/latin-700.css';
import '@fontsource/fraunces/latin-500.css';
import '@fontsource/fraunces/latin-600.css';
import '@fontsource/fraunces/latin-700.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'FERPLEC';

createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#D9232D',
        showSpinner: true,
    },
});