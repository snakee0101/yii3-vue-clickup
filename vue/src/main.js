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
import Aura from '@primeuix/themes/nora';
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import Tree from 'primevue/tree';
import Textarea from 'primevue/textarea';
import TreeTable from 'primevue/treetable';
import Column from 'primevue/column';
import Select from "primevue/select";
import DatePicker from 'primevue/datepicker';


//Unicons icons docs: https://iconscout.com/unicons/thin-line-icons/plus,  https://github.com/antonreshetov/vue-unicons
//example of styling: <unicon name="plus" fill="#ffffff" stroke="#ffffff"></unicon>
import Unicon from 'vue-unicons';
import { uniPlus, uniTachometerFast } from 'vue-unicons/dist/icons.js'

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
    .use(ToastService)

//My own
app.component('auth-layout', AuthLayout)
   .component('default-layout', DefaultLayout)

//PrimeVue
app.component('Button', Button);
app.component('InputText', InputText);
app.component('Dialog', Dialog);
app.component('Toast', Toast);
app.component('Tree', Tree);
app.component('Textarea', Textarea);
app.component('TreeTable', TreeTable);
app.component('Column', Column);
app.component('Select', Select);
app.component('DatePicker', DatePicker);

//Unicons
Unicon.add([uniPlus, uniTachometerFast]);
app.use(Unicon,{
    height: 16,
    width: 16
});

app.mount('#app')
