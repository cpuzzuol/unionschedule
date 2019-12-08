/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('@mdi/font/css/materialdesignicons.css')
const moment = require('moment')
const axios = require('axios')

// const CSRFToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
// axios.defaults.headers.common['X-CSRF-TOKEN'] = CSRFToken;

window.Vue = require('vue');
window.Vuetify = require('vuetify');
window.Vuelidate = require('vuelidate');
Vue.use(Vuetify)
Vue.use(Vuelidate)
Vue.prototype.$moment = moment
Vue.prototype.$http = axios


Vue.filter('slashdatetime', function (value) {
    if (!value) return ''
    let dt = Vue.prototype.$moment(value) // moment JS
    return dt.format("MM/DD/YYYY @ h:mm A")
})
Vue.filter('slashdate', function (value) {
    if (!value) return ''
    let dt = Vue.prototype.$moment(value) // moment JS
    return dt.format("MM/DD/YYYY")
})
Vue.filter('slashdateabbrev', function (value) {
    if (!value) return ''
    let dt = Vue.prototype.$moment(value) // moment JS
    return dt.format("M/D/YY")
})
Vue.filter('slashdatedow', function (value) {
    if (!value) return ''
    let dt = Vue.prototype.$moment(value) // moment JS
    return dt.format("ddd, MM/DD/YYYY")
})

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

//const files = require.context('./', true, /\.vue$/i)
//files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('vacation-selection', require('./components/VacationSelection.vue').default);
Vue.component('system-users', require('./components/SystemUsers.vue').default);
Vue.component('admin-home', require('./components/AdminHome.vue').default);
Vue.component('user-dashboard', require('./components/UserDashboard.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    vuetify: new Vuetify({
        iconfont: 'mdi'
    }),
    mounted() {
        console.log('Main container created.')
    },
    data: () => ({
        drawer: false,
    }),
    methods: {
        // #logout-form defined in app.blade.php
        logout() {
            document.getElementById('logout-form').submit();
        }
    }
});

// PREVENT FLASH OF UNSTYLED CONTENT ("FOUC") ON PAGE LOAD (requires class="no-js" in html element in app.blade.php and styles in app.scss)
(function(H){H.className=H.className.replace(/\bno-js\b/,'js')})(document.documentElement)
