import { runMiddlewarePipeline } from '@/router/hooks/runMiddlewarePipeline'
import type { Router } from 'vue-router'

export const guards = ({ router }: { router: Router }): void => {
  router.beforeEach(runMiddlewarePipeline({ router }))
}
