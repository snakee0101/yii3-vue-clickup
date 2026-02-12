import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import '@/css/main.css'
import router from "./router"

const app = createApp(App)

app.use(createPinia())
   .use(router)

app.mount('#app')
