import axiosInstance, { AxiosError, AxiosResponse, HttpStatusCode, isAxiosError } from '@/helpers/axiosInstance';
import { cloneDeep, isEqual } from 'lodash';
import { UnwrapNestedRefs, reactive } from 'vue';
import { watch } from 'vue';

interface IFormSubmitOptions {
  onStart?: () => any
  onError?: (errors: any) => any
  onSuccess?: () => any
  onFinish?: () => any
}

type TFormDataBase = Record<string, any>;
type TFormSubmitMethod = 'post' | 'put' | 'patch' | 'delete'
type TTransformCallback<TFormData extends TFormDataBase> = (data: TFormData) => TFormData | any

interface IFormWrapperProps<TFormData extends TFormDataBase> {
  isDirty: boolean
  errors: Partial<Record<keyof TFormData, string> | any>
  message?: string
  recentlySuccessful: boolean
  hasErrors: boolean
  processing: boolean
  data(): TFormData
  transform: (callback: TTransformCallback<TFormData>) => IFormWrapperProps<TFormData>
  reset: (...fields: (keyof TFormData)[]) => IFormWrapperProps<TFormData>
  clearErrors: (...fields: (keyof TFormData)[]) => IFormWrapperProps<TFormData>
  submit: <TResponse = any>(method: TFormSubmitMethod, url: string, options?: IFormSubmitOptions) => Promise<TResponse>
  post: <TResponse = any>(url: string, options?: IFormSubmitOptions) => Promise<TResponse>
  put: <TResponse = any>(url: string, options?: IFormSubmitOptions) => Promise<TResponse>
  patch: <TResponse = any>(url: string, options?: IFormSubmitOptions) => Promise<TResponse>
  delete: <TResponse = any>(url: string, options?: IFormSubmitOptions) => Promise<TResponse>
}

const RECENTLY_SUCCESSFUL_TIMEOUT = 2000

export type FormWrapper<TFormData extends TFormDataBase> = UnwrapNestedRefs<TFormData & IFormWrapperProps<TFormData>>

export function useForm<TFormData extends TFormDataBase>(data: TFormData): FormWrapper<TFormData>

export function useForm<TFormData extends TFormDataBase>(initialData: TFormData): FormWrapper<TFormData> {
  const data = cloneDeep(initialData)
  const defaults = cloneDeep(initialData)
  let recentlySuccessfulTimeoutId: any = null
  let transform = (data: any) => data

  const form = reactive({
    ...data,
    isDirty: false as boolean,
    errors: {} as Partial<Record<keyof TFormData, string> | any>,
    message: '',
    hasErrors: false as boolean,
    processing: false as boolean,
    recentlySuccessful: false as boolean,

    data() {
      return (Object.keys(data) as (keyof TFormData)[]).reduce((carry, key) => {
        // @ts-expect-error
        carry[key] = this[key]
        return carry
      }, {} as Partial<TFormData>) as TFormData
    },

    transform(callback: (data: TFormData) => TFormData | any) {
      transform = callback

      return this
    },

    reset(...fields: (keyof TFormData)[]) {
      if (fields.length === 0) {
        Object.assign(this, defaults)
      } else {
        Object.keys(defaults)
          .filter((key) => fields.includes(key))
          .forEach((key) => {
            this[key] = defaults[key]
          })
      }

      return this
    },

    clearErrors(...fields: (keyof TFormData)[]) {
      this.errors = Object.keys(this.errors).reduce(
        (carry, field) => ({
          ...carry,
          ...(fields.length > 0 && !fields.includes(field) ? { [field]: this.errors[field] } : {}),
        }),
        {},
      )

      this.hasErrors = Object.keys(this.errors).length > 0
      this.message = ''

      return this
    },

    async submit<TResponse = any>(
      method: TFormSubmitMethod,
      url: string,
      options?: IFormSubmitOptions,
    ): Promise<TResponse> {
      const payload = transform(this.data())

      clearTimeout(recentlySuccessfulTimeoutId)

      this.processing = true
      options?.onStart?.()

      return axiosInstance<TResponse>({
          method,
          url,
          data: payload,
        })
        .catch((error: Error | AxiosError) => {
          form.hasErrors = true

          if (isAxiosError(error) && error.response?.status == HttpStatusCode.UnprocessableEntity) {
            if (error.response.data.errors) {
              const respErrors = error.response.data.errors
              Object.keys(respErrors).forEach((key) => form.errors[key] = respErrors[key]?.join?.( '') ?? respErrors[key])
            }

            form.message = error.response.data?.message ?? ''
          }

          if (options?.onError) {
            options?.onError?.(error)
          }

          throw error
        })
        .then((response: AxiosResponse) => {
          this.clearErrors()
          this.recentlySuccessful = true
          recentlySuccessfulTimeoutId = setTimeout(() => (this.recentlySuccessful = false), RECENTLY_SUCCESSFUL_TIMEOUT)
          this.isDirty = false
          options?.onSuccess?.()
          return response.data
        })
        .finally(() => {
          this.processing = false
          options?.onFinish?.()
        })
    },

    async post<TResponse = any>(url: string, options?: IFormSubmitOptions) {
      return this.submit<TResponse>('post', url, options)
    },
    async put<TResponse = any>(url: string, options?: IFormSubmitOptions) {
      return this.submit<TResponse>('post', url, options)
    },
    async patch<TResponse = any>(url: string, options?: IFormSubmitOptions) {
      return this.submit<TResponse>('patch', url, options)
    },
    async delete<TResponse = any>(url: string, options?: IFormSubmitOptions) {
      return this.submit<TResponse>('delete', url, options)
    },
  })

  watch(
    form,
    () => {
      form.isDirty = !isEqual(form.data(), defaults)
    },
    { immediate: true, deep: true },
  )

  return form
}
