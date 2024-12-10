require('./bootstrap');
require('./filters');
require('./window');
require('./translations');
require('./admin_specific');

import store from './store'

const files = require.context('./vue/admin/components', true, /\.vue$/i);
files.keys().map(key =>
    Vue.component(key.split('/').pop().split('.')[0], files(key).default)
);

import VueRouter from 'vue-router'
Vue.use(VueRouter)

import panZoom from 'vue-panzoom'
Vue.use(panZoom);

import EloquentList from './vue/admin/views/EloquentList'
import Translation from './vue/admin/views/Translation'
import Dashboard from './vue/admin/views/Dashboard'
import Order from './vue/admin/views/Order'
import Timetable from './vue/admin/views/Timetable'
import TimetableScans from './vue/admin/views/TimetableScans'
import DrawVenue from './vue/admin/views/DrawVenue'
import DrawSection from './vue/admin/views/DrawSection'
import Report from './vue/admin/views/Report'
import Subscriptions from './vue/admin/views/Subscriptions'
import Configs from './vue/admin/views/Configs'
import GeneralConfig from './vue/admin/views/GeneralConfig'

const routes = [
    { path: '/', redirect: '/show' },
    { path: '/dashboard', name: 'dashboard', component: Dashboard },
    { path: '/eloquent_configs', name: 'eloquent_configs', component: Configs },
    { path: '/general_config', name: 'general_config', component: GeneralConfig },
    { path: '/translation', name: 'translation', component: Translation },
    { path: '/subscriptions', name: 'subscriptions', component: Subscriptions },
    { path: '/order/:orderid', name: 'order', component: Order },
    { path: '/timetable/:id', name: 'timetable', component: Timetable },
    { path: '/timetable/:id/scans', name: 'timetable_scans', component: TimetableScans },
    { path: '/scheme/:id', name: 'venue', component: DrawVenue },
    { path: '/report', name: 'report', component: Report },
    { path: '/section/:id', name: 'section', component: DrawSection },
    { path: '/:model', name: 'eloquent_list', component: EloquentList },
]

const router = new VueRouter({
    // mode: 'history',
    routes,
    scrollBehavior (to, from, savedPosition) {
        return { x: 0, y: 0 }
    }
});

router.beforeEach((to, from, next) => {
    window.loaderIcon()
    next()
})


const app = new Vue({
    el: '#app',
    router,
    store,
    watch:{
        $route (to, from){

        }
    }
});
