import { defineStore } from 'pinia'
import { User } from '@/types/user.type'
import { RemovableRef, useSessionStorage } from '@vueuse/core'
import axiosInstance from '@/helpers/axiosInstance'
import { computed } from 'vue'
import { RouteMap } from '@/router/routeMap'

export const useAuthStore = defineStore(
  'authStore',
  () => {
    const user: RemovableRef<User | undefined> = useSessionStorage('auth:user', undefined)
    const isAuthenticated = computed<boolean>(() => !!user.value)

    const loadUserData = async () => {
      const { data } = await axiosInstance.get<User>('/api/user')
      user.value = data
    }

    const logout = () => axiosInstance.post(RouteMap.API.LOGOUT).finally(() => user.value = null)

    return {
      isAuthenticated,
      user,
      loadUserData,
      logout,
    }
  },
)
