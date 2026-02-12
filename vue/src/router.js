import { createMemoryHistory, createRouter } from 'vue-router'

import GoalsView from './pages/Goals.vue'
import SpacesView from './pages/Spaces.vue'

const routes = [
  { path: '/', component: SpacesView },
  { path: '/goals', component: GoalsView },
]

const router = createRouter({
  history: createMemoryHistory(),
  routes,
})

export default router