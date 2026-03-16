import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';

const fallbackAppName = import.meta.env.VITE_APP_NAME || 'Laravel';

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

createServer(
    (page) => {
        const appName = resolveAppName(page.props);

        return createInertiaApp({
            page,
            render: renderToString,
            title: (title) => (title ? `${title} - ${appName}` : appName),
            resolve: (name) =>
                resolvePageComponent(
                    `./pages/${name}.vue`,
                    import.meta.glob<DefineComponent>('./pages/**/*.vue'),
                ),
            setup: ({ App, props, plugin }) =>
                createSSRApp({ render: () => h(App, props) }).use(plugin),
        });
    },
    { cluster: true },
);
