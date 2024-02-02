import { createApp } from 'vue'
import pinia from '@/stores'
import App from '@/App.vue'
import router from '@/router'
import '@/css/app.css'

import { i18nPlugin } from '@/plugins/i18n'
import ToastPlugin from 'vue-toast-notification'
import 'vue-toast-notification/dist/theme-sugar.css'
import '@fortawesome/fontawesome-free/css/all.css'

const app = createApp(App)

app.use(router)
app.use(pinia)
app.use(i18nPlugin)
app.use(ToastPlugin)

app.mount('#app')
