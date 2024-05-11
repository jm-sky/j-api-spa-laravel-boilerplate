import { RouteMap } from '@/router/routeMap'
import { useAuthStore } from '@/stores'

export default ({ next }: any) => {
  const authStore = useAuthStore()

  if (authStore.isAuthenticated) {
    return next(RouteMap.HOME)
  }

  return next()
}
