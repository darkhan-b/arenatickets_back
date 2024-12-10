// window._ = require('lodash');

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    window.bootstrap = require('bootstrap');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

let api_token = document.head.querySelector('meta[name="api-token"]');

if(api_token && api_token.content) {
    // window.axios.defaults.headers.common['Authorization'] = 'Bearer '+api_token.content;
    window.axios.defaults.headers.common['X-API-KEY'] = api_token.content;
}

let b_token = window.localStorage.getItem('btoken', null);
if(b_token) {
    window.axios.defaults.headers.common['Authorization'] = 'Bearer '+b_token;
}

window.Vue = require('vue').default;

Vue.prototype.window = window;

window.csrf_field = function() {
    return '<input type="hidden" value="'+window.axios.defaults.headers.common['X-CSRF-TOKEN']+'" name="_token"/>';
}
