import { createApp } from 'vue';
import { i18nPlugin } from '@/plugins/i18n'
import pinia from '@/stores'
import App from '@/App.vue';
import router from '@/router'

const app = createApp(App)

app.use(router)
app.use(pinia)
app.use(i18nPlugin)

app.mount('#app')
