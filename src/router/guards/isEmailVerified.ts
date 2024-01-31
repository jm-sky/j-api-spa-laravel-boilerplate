import { RouteMap } from '@/router/types/routeMap'
import { useAuthStore } from '@/stores'

export default ({ next }: any) => {
  const redirectPath = RouteMap.EMAIL_NOT_VERIFIED
  const authStore = useAuthStore()

  if (!authStore.user?.verified) {
    return next(redirectPath)
  }

  return next()
}
