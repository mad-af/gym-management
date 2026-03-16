import { createInertiaApp, router } from '@inertiajs/vue3';
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

const fallbackAppName = import.meta.env.VITE_APP_NAME || 'Laravel';
let appName = fallbackAppName;

const resolveAppName = (props: unknown): string => {
    if (props && typeof props === 'object' && 'app' in props) {
        const app = props.app;
        if (
            app &&
            typeof app === 'object' &&
            'name' in app &&
            typeof app.name === 'string' &&
            app.name.trim()
        ) {
            return app.name;
        }
    }

    return fallbackAppName;
};

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        appName = resolveAppName(props.initialPage.props);
        router.on('navigate', (event) => {
            appName = resolveAppName(event.detail.page.props);
        });

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(VueApexCharts);

        app.directive('can', canDirective);

        // Register global components
        app.component(
            'admin-layout',
            () => import('./components/layout/AdminLayout.vue'),
        );
        app.component(
            'full-screen-layout',
            () => import('./components/layout/FullScreenLayout.vue'),
        );

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
// initializeTheme(); // Disabled in favor of ThemeProvider
