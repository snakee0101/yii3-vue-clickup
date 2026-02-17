import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import '@/css/main.css'
import router from "./router"
import AuthLayout from './pages/layouts/AuthLayout.vue'
import DefaultLayout from './pages/layouts/DefaultLayout.vue'

//PrimeVue
//Primevue docs - https://primevue.org/listbox/
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import Button from "primevue/button";

//Unicons icons docs: https://iconscout.com/unicons/thin-line-icons/plus,  https://github.com/antonreshetov/vue-unicons
//example of styling: <unicon name="plus" fill="#ffffff" stroke="#ffffff"></unicon>
import Unicon from 'vue-unicons';
import { uniPlus } from 'vue-unicons/dist/icons.js'

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

//Unicons
Unicon.add([uniPlus]);
app.use(Unicon,{
    height: 16,
    width: 16
});

app.mount('#app')
