import { RouteMap } from '@/router/routeMap'
import isLogged from '@/router/guards/isLogged'
import isNotGuest from '@/router/guards/isNotGuest'
import isEmailVerified from '@/router/guards/isEmailVerified'
import type { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'welcome',
    component: async () => await import('@/pages/Welcome.vue'),
    meta: {
      middlewares: [isLogged],
    },
  },
  {
    path: RouteMap.HOME,
    name: 'dashboard',
    component: async () => await import('@/pages/Dashboard.vue'),
    meta: {
      // middlewares: [isLogged],
    },
  },
  {
    path: RouteMap.LOGIN,
    name: 'login',
    component: async () => await import('@/pages/Auth/Login.vue'),
    meta: {
      // middlewares: [isNotGuest],
    },
  },
  {
    path: RouteMap.LOGOUT,
    name: 'logout',
    component: async () => await import('@/pages/Auth/Logout.vue'),
    meta: {
      // middlewares: [isNotGuest],
    },
  },
  {
    path: RouteMap.REGISTER,
    name: 'register',
    component: async () => await import('@/pages/Auth/Register.vue'),
    meta: {
      // middlewares: [isNotGuest],
    },
  },
  // {
  //   path: '/',
  //   children: [

  //     {
  //       path: RouteMap.PASSWORD_FORGOT,
  //       name: 'forgot-password',
  //       component: async () => await import('@/pages/Auth/ForgotPasswordView.vue'),
  //       meta: {
  //         middlewares: [isNotGuest],
  //       },
  //     },
  //     {
  //       path: RouteMap.PASSWORD_RESET,
  //       name: 'reset-password',
  //       component: async () => await import('@/pages/Auth/PasswordResetView.vue'),
  //       meta: {
  //         middlewares: [isNotGuest],
  //       },
  //     },
  //     {
  //       path: RouteMap.EMAIL_VERIFY,
  //       name: 'email-verify',
  //       component: async () => await import('@/pages/Auth/EmailVerifyView.vue'),
  //       meta: {
  //         middlewares: [],
  //       },
  //     },
  //     {
  //       path: RouteMap.EMAIL_NOT_VERIFIED,
  //       name: 'email-not-verified',
  //       component: async () => await import('@/pages/Auth/EmailNotVerifiedView.vue'),
  //       meta: {
  //         middlewares: [isLogged],
  //       },
  //     },
  //   ],
  // },
  {
    path: '/:pathMatch(.*)*',
    name: '404',
    component: async () => await import('@/pages/Error404.vue'),
  },
]

export { routes }
