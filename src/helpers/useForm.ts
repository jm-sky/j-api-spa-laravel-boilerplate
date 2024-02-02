import axiosInstance from '@/helpers/axiosInstance';
import { refAutoReset } from '@vueuse/core';
import axios, { AxiosError, HttpStatusCode } from 'axios';
import { reactive } from 'vue';
import { UnwrapRef } from 'vue';
import { ref } from 'vue'

interface IFormOptions {
  onStart?: () => any
  onError?: (errors: any) => any
  onSuccess?: () => any
  onFinish?: () => any
}

export function useForm<TData extends {}>(initialData: TData) {
  const processing = ref(false)
  let errors = reactive<Partial<Record<keyof TData, string> | any>>({})
  const message = ref('')
  const hasError = ref(false)
  const recentlySuccessful = refAutoReset(false, 2000)
  const data = ref(structuredClone(initialData))
  const defaults = ref(structuredClone(initialData))

  const catchErrors = (onError?: (errors: any) => any) => (error: Error | AxiosError) => {
    hasError.value = true

    if (axios.isAxiosError(error) && error.response?.status == HttpStatusCode.UnprocessableEntity) {
      if (error.response.data.errors) {
        const respErrors = error.response.data.errors
        Object.keys(respErrors).forEach((key) => errors[key] = respErrors[key]?.join?.( '') ?? respErrors[key])
      }
      if (error.response.data.message) message.value = error.response.data.message

      return error.response.data
    }

    if (onError) {
      onError(error)
    }

    throw error
  }

  return {
    ...data.value,
    processing: processing.value,
    errors,
    message: message.value,
    hasError: () => hasError.value,
    clearErrors: () => errors = reactive<Partial<Record<keyof TData, string> | any>>({}),
    recentlySuccessful: () => recentlySuccessful.value,
    reset: (...fields: Array<keyof UnwrapRef<TData>>) => {
      if (!fields?.length) {
        data.value = structuredClone(defaults.value)
        return
      } else {
        fields.forEach(field => data.value[field] = defaults.value[field])
      }
    },
    post: async <TResponse>(url: string, options?: IFormOptions) => {
      processing.value = true
      options?.onStart?.()
      return axiosInstance.post<TResponse>(url, data.value)
        .catch((error: Error | AxiosError<unknown, any>) => catchErrors(options?.onError)(error))
        .then(response => {
          hasError.value = false
          recentlySuccessful.value = true
          options?.onSuccess?.()
          return response.data
        })
        .finally(() => {
          processing.value = false
          options?.onFinish?.()
        })
    },
    put: async <TResponse>(url: string, options?: IFormOptions) => {
      processing.value = true
      options?.onStart?.()
      return axiosInstance.post<TResponse>(url, data.value)
        .catch(catchErrors(options?.onError))
        .then(response => {
          hasError.value = false
          recentlySuccessful.value = true
          options?.onSuccess?.()
          return response.data
        })
        .finally(() => {
          processing.value = false
          options?.onFinish?.()
        })
    },
  }
}
