import { createWebHistory, createRouter } from 'vue-router'

import GoalsView from './pages/Goals.vue'
import SpacesView from './pages/Spaces.vue'
import LoginView from './pages/auth/Login.vue'
import RegisterView from './pages/auth/Register.vue'

const routes = [
  { path: '/', component: SpacesView },
  { path: '/goals', component: GoalsView },
  { path: '/login', component: LoginView },
  { path: '/register', component: RegisterView }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router