require('./bootstrap')

import Vue from 'vue'

import { InertiaApp } from '@inertiajs/inertia-vue'
import { InertiaForm } from 'laravel-jetstream'
import PortalVue from 'portal-vue'

Vue.use(InertiaApp)
Vue.use(InertiaForm)
Vue.use(PortalVue)

// Custom Text Filters
Vue.filter('commas', function (value) {
    if (!value) return ''
    return value.toLocaleString()
})

Vue.filter('pct0', function (value) {
    if (!value) return ''
    let percent = value * 100
    return Math.round(percent) + '%'
})

Vue.filter('pct1', function (value) {
    if (!value) return ''
    let percent = value * 100
    return percent.toFixed(1) + '%'
})

const app = document.getElementById('app')

new Vue({
    render: (h) =>
        h(InertiaApp, {
            props: {
                initialPage: JSON.parse(app.dataset.page),
                resolveComponent: (name) => require(`./Pages/${name}`).default,
            },
        }),
}).$mount(app)
