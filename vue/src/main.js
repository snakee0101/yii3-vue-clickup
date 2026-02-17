import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import '@/css/main.css'
import router from "./router"
import AuthLayout from './pages/layouts/AuthLayout.vue'
import DefaultLayout from './pages/layouts/DefaultLayout.vue'

//PrimeVue
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import Button from "primevue/button";

//axios settings
import axios from 'axios'
if(localStorage.getItem('access_token') !== null) {
    axios.defaults.headers.common['Content-Type'] = 'application/json';
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('access_token');
}
window.axios = axios;

//app
const app = createApp(App)

app.use(createPinia())
   .use(router)
   .use(PrimeVue, {
       theme: {
           preset: Aura
       }
   })

//My own
app.component('auth-layout', AuthLayout)
   .component('default-layout', DefaultLayout)

//PrimeVue
app.component('Button', Button);

app.mount('#app')
