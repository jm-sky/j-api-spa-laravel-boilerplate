import axiosInstance from '@/helpers/axiosInstance';
import axios, { AxiosError, HttpStatusCode } from 'axios';
import { ref } from 'vue'


export function useForm<TData extends {}>(data: TData) {
  const processing = ref(false)
  const errors = ref<Partial<Record<keyof TData, string>>>({})

  function catchErrors(error: Error | AxiosError) {
    if (axios.isAxiosError(error) && error.response?.status == HttpStatusCode.UnprocessableEntity) {
      errors.value = error.response.data

      return error.response.data
    }
    throw error
  }

  return {
    ...data,
    processing,
    errors: errors.value,
    get: async <TResponse>(url: string) => {
      processing.value = true
      return axiosInstance.get<TResponse>(url)
        .catch(catchErrors)
        .then(response => response.data)
        .finally(() => processing.value = false)
    },
    post: async <TResponse>(url: string) => {
      processing.value = true
      return axiosInstance.post<TResponse>(url, data)
        .catch(catchErrors)
        .then(response => response.data)
        .finally(() => processing.value = false)
    },
    put: async <TResponse>(url: string) => {
      processing.value = true
      return axiosInstance.put<TResponse>(url, data)
        .catch(catchErrors)
        .then(response => response.data)
        .finally(() => processing.value = false)
    },
  }
}
