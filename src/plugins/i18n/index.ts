import { createI18n } from 'vue-i18n'
import en from '@/plugins/i18n/en'

export enum ELocale {
  English = 'en',
}

export const i18nPlugin = createI18n({
  legacy: false,
  locale: ELocale.English,
  fallbackLocale: ELocale.English,
  messages: {
    en,
  },
})

export { en }
