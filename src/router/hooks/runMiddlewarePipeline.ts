import middlewarePipeline from '@/router/helpers/middlewarePipeline'
import type { INextPipeline } from '@/router/helpers/middlewarePipeline'
import type { NavigationGuardNext, RouteLocationNormalized, RouteLocationRaw, Router } from 'vue-router'

export type NavigationGuardReturn = void | Error | RouteLocationRaw | boolean

export interface IRunMiddlewarePipeline {
  router: Router
}

export interface IMiddlewareOptions {
  to: RouteLocationNormalized
  from: RouteLocationNormalized
  next: NavigationGuardNext | INextPipeline
  router: Router
}

export type IMiddleware = (options: IMiddlewareOptions) => NavigationGuardReturn

export const runMiddlewarePipeline =
  ({ router }: IRunMiddlewarePipeline) =>
    (to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext): NavigationGuardReturn => {
      const middlewares = to.meta?.middlewares as IMiddleware[]
      const firstMiddleware = middlewares?.[0]

      if (!firstMiddleware) {
        return next()
      }

      const context = {
        to,
        from,
        next,
        router,
      }

      return firstMiddleware({ ...context, next: middlewarePipeline(context, middlewares, 1) })
    }
