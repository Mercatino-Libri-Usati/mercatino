// main.js
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { apiClient } from '@/apiConfig'
import { useAuthStore } from '@/stores/authStore'
import '@mdi/font/css/materialdesignicons.css'

// Vuetify
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as directives from 'vuetify/directives'
import {
  VBtn,
  VContainer,
  VIcon,
  VTextField,
  VRow,
  VCol,
  VChip,
  VChipGroup,
  VTable,
  VDivider,
  VCard,
  VCardTitle,
  VCardText,
  VCardActions,
  VProgressCircular,
  VSpacer,
  VDialog,
  VSelect,
  VForm,
  VExpansionPanels,
  VExpansionPanel,
  VExpansionPanelTitle,
  VExpansionPanelText,
} from 'vuetify/components'

// Vue Toastification
import Vue3Toastify, { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

const vuetify = createVuetify({
  components: {
    VForm,
    VBtn,
    VContainer,
    VIcon,
    VTextField,
    VRow,
    VCol,
    VChip,
    VChipGroup,
    VTable,
    VDivider,
    VCard,
    VCardTitle,
    VCardText,
    VCardActions,
    VProgressCircular,
    VSpacer,
    VDialog,
    VSelect,
    VExpansionPanels,
    VExpansionPanel,
    VExpansionPanelTitle,
    VExpansionPanelText,
  },
  directives,
  icons: {
    defaultSet: 'mdi',
  },
  theme: {
    defaultTheme: 'light',
  },
})

const app = createApp(App)

// Pinia
const pinia = createPinia()
app.use(pinia)
app.use(router)
app.use(vuetify)

// Configura Vue3Toastify
app.use(Vue3Toastify, {
  autoClose: 3000,
  position: toast.POSITION.BOTTOM_RIGHT,
  pauseOnHover: false,
})

// --- Interceptor 401 ---
const authStore = useAuthStore(pinia)
let isHandling401 = false
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response && error.response.status === 401) {
      if (!isHandling401) {
        isHandling401 = true
        try {
          if (authStore.isAuthenticated) {
            await authStore.logout()
          }
          if (router.currentRoute.value.name !== 'login') {
            router.push({ name: 'login' })
          }
        } finally {
          setTimeout(() => {
            isHandling401 = false
          }, 2000)
        }
      }
    }
    return Promise.reject(error)
  },
)

app.mount('#app')
