import { useAuthStore } from '@/stores'
import { RouteMap } from '@/router/routeMap'
// import { authService } from '@/modules/Auth/services/auth.service'

export default async ({ to, next }: any) => {
  const authStore = useAuthStore()
  const redirectPath = RouteMap.LOGIN
  const redirectQuery = { path: redirectPath, query: { redirect: to.fullPath } }

  if (!authStore.isAuthenticated) {
    try {
      // await authService.getAndSaveAuthUser()

      if (!authStore.isAuthenticated) {
        return next(redirectQuery)
      }

      return next()
    } catch (error: unknown) {
      return next(redirectQuery)
    }
  } else {
    return next()
  }
}
