import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { Ziggy } from './ziggy-routes.js';
import { createPinia } from 'pinia'

const pinia = createPinia()

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // URL для route() — с сервера или текущий origin, иначе ссылки ведут на домен из ziggy-routes.js
        const serverZiggy = (typeof window !== 'undefined' && window.page?.props?.ziggy) || props?.initial?.page?.props?.ziggy || props?.ziggy;
        const currentOrigin = typeof window !== 'undefined' ? window.location.origin : Ziggy.url;
        const ziggyConfig = { ...Ziggy, url: (serverZiggy && serverZiggy.url) || currentOrigin, ...serverZiggy };
        const myApp =  createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue, ziggyConfig);

        myApp.config.globalProperties.appName = appName;

        myApp.mount(el);

        return myApp;
    },
    progress: {
        color: '#4B5563',
    },
});
