// window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    // require('bootstrap');
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
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });

// window.Vue = require('vue');
window.Vue = require('vue').default;

Vue.prototype.window = window;

window.csrf_field = function() {
    return '<input type="hidden" value="'+window.axios.defaults.headers.common['X-CSRF-TOKEN']+'" name="_token"/>';
}
