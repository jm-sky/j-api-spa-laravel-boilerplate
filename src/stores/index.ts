import { createPinia, setMapStoreSuffix } from 'pinia'
import type { Router } from 'vue-router'

declare module 'pinia' {
  export interface PiniaCustomProperties {
    readonly router: Router
  }
  export interface MapStoresCustomization {
    suffix: ''
  }
}

setMapStoreSuffix('')

const pinia = createPinia()

export default pinia

export * from '@/stores/modules/auth.store'
