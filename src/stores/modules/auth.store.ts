import { defineStore } from 'pinia'
import { type Ref, ref } from 'vue'
import { User } from '@/types/user.type'

export const useAuthStore = defineStore(
  'authStore',
  () => {
    const isAuthenticated = ref(true)
    const user: Ref<User | undefined> = ref({
      id: '1234-abcd',
      name: 'John',
      email: 'john@example.com',
    })

    return {
      isAuthenticated,
      user,
    }
  },
)
