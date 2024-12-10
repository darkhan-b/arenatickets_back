require('./widget_bootstrap');
require('./translations');
require('./filters');
require('./window');

import store from './store'

const files = require.context('./vue/app/components', true, /\.vue$/i);
files.keys().map(key =>
    Vue.component(key.split('/').pop().split('.')[0], files(key).default)
);

import VueMeta from 'vue-meta'
Vue.use(VueMeta)

import VueRouter from 'vue-router'
Vue.use(VueRouter)

import panZoom from 'vue-panzoom'
Vue.use(panZoom)

import OtpInput from "@bachdgvn/vue-otp-input"
Vue.component("v-otp-input", OtpInput)

import WidgetInit from './vue/app/components/WidgetInit'
import Event from './vue/app/components/Event'
import Timetable from './vue/app/components/Timetable'
import Checkout from './vue/app/components/Checkout'
import TestPage from './vue/app/components/TestPage'

const routes = [
    // { path: '/', redirect: '/dashboard' },
    { path: '/', name: 'widgetinit', component: WidgetInit },
    { path: '/test', name: 'checkout', component: TestPage },
    { path: '/:eventid', name: 'event', component: Event },
    { path: '/:eventid/:timetableid', name: 'timetable', component: Timetable },
    { path: '/order/:id/:hash', name: 'checkout', component: Checkout },
    
]

const router = new VueRouter({
    // mode: 'history',
    routes,
    scrollBehavior (to, from, savedPosition) {
        return { x: 0, y: 0 }
    }
});

// router.beforeEach((to, from, next) => {
//     next()
// })

const load = () => {
    new Vue({
        el: '#vue-event',
        router,
        store,
        mounted() {
            if(this.$route.query && this.$route.query.testMode) {
                this.$store.commit('setTestMode', true)
            }
        }
    });
}

const arr = window.location.href.split('&');
let tkn = arr.find(x => x.includes('tkn='))
if(tkn) {
    tkn = tkn.replace('tkn=', '')
    window.localStorage.setItem('btoken', tkn);
    window.axios.defaults.headers.common['Authorization'] = 'Bearer '+tkn;
}
let showId = arr.find(x => x.includes('show_id='))
if(showId) showId = showId.replace('show_id=', '')

store.dispatch('getSettings', { showId }).then(() => {
    load()
})
