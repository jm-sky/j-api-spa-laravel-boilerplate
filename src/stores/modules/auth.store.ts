import { defineStore } from 'pinia'
import { User } from '@/types/user.type'
import { RemovableRef, useSessionStorage } from '@vueuse/core'
import axiosInstance from '@/helpers/axiosInstance'
import { computed } from 'vue'

const DEFAULT_USER: User = {
  id: '1234-abcd',
  name: 'John',
  email: 'john@example.com',
  verified: true,
  emailVerifiedAt: '2024-01-01 10:00',
}

export const useAuthStore = defineStore(
  'authStore',
  () => {
    const user: RemovableRef<User | undefined> = useSessionStorage('auth:user', DEFAULT_USER)
    const isAuthenticated = computed<boolean>(() => !!user.value)

    const loadUserData = async () => {
      const { data } = await axiosInstance.get<User>('/api/user')
      user.value = data
    }

    return {
      isAuthenticated,
      user,
      loadUserData,
    }
  },
)
