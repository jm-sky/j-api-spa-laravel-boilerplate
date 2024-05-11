import type { IMiddleware, IMiddlewareOptions } from '@/router/hooks/runMiddlewarePipeline'
import type { NavigationGuardNext } from 'vue-router'

export type INextPipeline = NavigationGuardNext | ((params: any) => void)

const middlewarePipeline = (context: IMiddlewareOptions, middlewares: IMiddleware[], index: number): INextPipeline => {
  const nextMiddleware = middlewares[index]

  if (!nextMiddleware) {
    return context.next
  }

  return (params: any)=> {
    if (params) return context.next(params)

    const nextPipeline: any = middlewarePipeline(context, middlewares, index + 1)

    return nextMiddleware({ ...context, next: nextPipeline }) as NavigationGuardNext
  }
}

export default middlewarePipeline
