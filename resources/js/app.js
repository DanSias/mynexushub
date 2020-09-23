require('./bootstrap')

import Vue from 'vue'

import { InertiaApp } from '@inertiajs/inertia-vue'
import { InertiaForm } from 'laravel-jetstream'
import PortalVue from 'portal-vue'
import VueMeta from 'vue-meta'

Vue.use(InertiaApp)
Vue.use(InertiaForm)
Vue.use(PortalVue)
Vue.use(VueMeta)

// Custom Text Filters
Vue.filter('commas', function (value) {
    if (!value) return ''
    return value.toLocaleString()
})
// Abbreaviate Large Numbers
Vue.filter('bigCommas', function (number) {
    if (typeof number === 'string' || number instanceof String) {
        return number;
    }
  
    var value = parseInt(number);
  
    if (isNaN(value)) {
        return 0;
    } else {
        if (value > 1000000) {
            return (value / 1000000).toFixed(1) + 'M';
        } else if (value > 100000) {
            return Math.round(value / 1000) + 'k';
        } else if (value > 20000) {
            return (value / 1000).toFixed(1) + 'k';
        }
    
        return value.toLocaleString();
    }
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
// Date - Full Month, No Leading Zeros
Vue.filter('dateFj', function(date) {
    if (date === undefined || ! date) {
        return ''
    }
    let dateString = date.split('T')[0]
    let dateArray = dateString.split('-');
    let day = parseInt(dateArray[2]);
    let monthIndex = dateArray[1] - 1;
    let months = [
        'January', 'February', 'March', 'April', 'May', 'June', 
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    return months[monthIndex] + ' ' + day;
});
// Date - Full Month, No Leading Zeros, Year
Vue.filter('dateFjy', function(date) {
    if (date === undefined || ! date) {
        return ''
    }
    let dateString = date.split('T')[0]
    let dateArray = dateString.split('-');
    let day = parseInt(dateArray[2]);
    let monthIndex = dateArray[1] - 1;
    let months = [
        'January', 'February', 'March', 'April', 'May', 'June', 
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    return `${months[monthIndex]} ${day}, ${dateArray[0]}`;
});

window.Vue = Vue
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
