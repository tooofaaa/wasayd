import '../css/app.css'

import { createApp, h, type DefineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createPinia } from 'pinia'
import { createI18n } from 'vue-i18n'
import { ZiggyVue } from 'ziggy-js'

import ar from './i18n/ar.json'
import en from './i18n/en.json'

const i18n = createI18n({
    legacy: false,
    locale: document.documentElement.lang || 'ar',
    fallbackLocale: 'en',
    messages: { ar, en },
})

const pinia = createPinia()

createInertiaApp({
    title: (title) => (title ? `${title} — WASAYD` : 'WASAYD'),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
        app.use(plugin)
        app.use(pinia)
        app.use(i18n)
        app.use(ZiggyVue)
        app.mount(el)
    },
    progress: {
        color: '#2563eb',
    },
})
