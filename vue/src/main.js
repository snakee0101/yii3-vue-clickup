import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import '@/css/main.css'
import router from "./router"
import AuthLayout from './pages/layouts/AuthLayout.vue'
import DefaultLayout from './pages/layouts/DefaultLayout.vue'

import axios from 'axios'
if(localStorage.getItem('access_token') !== null) {
    axios.defaults.headers.common['Content-Type'] = 'application/json';
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('access_token');
}
window.axios = axios;

const app = createApp(App)

app.use(createPinia())
   .use(router)

app.component('auth-layout', AuthLayout)
   .component('default-layout', DefaultLayout)

app.mount('#app')
