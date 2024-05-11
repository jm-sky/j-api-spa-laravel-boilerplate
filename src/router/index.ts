import type { RouteLocationNormalized } from 'vue-router'
import { createRouter, createWebHistory } from 'vue-router'
import { routes } from '@/router/routes'
import { guards } from '@/router/guards'

const router = createRouter({
  routes,
  history: createWebHistory(),
  async scrollBehavior(to: RouteLocationNormalized) {
    if (to.hash) {
      return await new Promise((resolve) => {
        setTimeout(() => resolve({ el: to.hash, behavior: 'smooth' }), 100)
      })
    }

    return await new Promise((resolve) => {
      setTimeout(() => resolve({ left: 0, top: 0 }), 100)
    })
  },
})

guards({ router })

export default router
