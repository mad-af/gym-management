import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import canDirective from './directives/can';
import './plugins/axios';
import '../css/app.css';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'jsvectormap/dist/jsvectormap.css';
import 'flatpickr/dist/flatpickr.css';
import 'simplebar-vue/dist/simplebar.min.css';

// import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(VueApexCharts);

        app.directive('can', canDirective);

        // Register global components
        app.component('admin-layout', () => import('./components/layout/AdminLayout.vue'));
        app.component('full-screen-layout', () => import('./components/layout/FullScreenLayout.vue'));

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
// initializeTheme(); // Disabled in favor of ThemeProvider
