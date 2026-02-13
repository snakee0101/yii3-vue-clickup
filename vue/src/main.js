import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import '@/css/main.css'
import router from "./router"
import AuthLayout from './pages/layouts/AuthLayout.vue'
import DefaultLayout from './pages/layouts/DefaultLayout.vue'

const app = createApp(App)

app.use(createPinia())
   .use(router)

app.component('auth-layout', AuthLayout)
   .component('default-layout', DefaultLayout)

app.mount('#app')
