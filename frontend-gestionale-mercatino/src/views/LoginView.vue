<template>
  <div class="login-wrapper d-flex align-center justify-center">
    <v-card class="login-card elevation-10" width="100%" max-width="420" rounded="xl">
      <v-card-title class="login-header d-flex flex-column align-center py-8 text-white">
        <v-icon size="48" class="mb-2">mdi-shield-account</v-icon>
        <h2 class="text-h5 font-weight-bold">Accedi al Gestionale</h2>
      </v-card-title>

      <v-card-text class="pa-8">
        <p class="text-center text-medium-emphasis mb-6">
          Inserisci le tue credenziali per continuare
        </p>

        <v-form ref="form" @submit.prevent="handleLogin">
          <v-text-field
            v-model="username"
            label="Email o Username"
            prepend-inner-icon="mdi-account-circle"
            placeholder="Il tuo username o la tua email"
            variant="outlined"
            class="mb-4"
            :rules="[(v) => !!v || 'Campo obbligatorio']"
            autocomplete="username"
          />

          <v-text-field
            v-model="password"
            label="Password"
            :type="showPassword ? 'text' : 'password'"
            prepend-inner-icon="mdi-lock"
            :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
            placeholder="La tua password"
            variant="outlined"
            class="mb-2"
            :rules="[(v) => !!v || 'Password obbligatoria']"
            @click:append-inner="showPassword = !showPassword"
            autocomplete="current-password"
          />

          <div class="text-center mb-6">
            <router-link
              :to="{ name: 'richiedi-link-password' }"
              class="text-primary text-decoration-none font-weight-medium"
            >
              Non hai o non ricordi la password?
            </router-link>
          </div>

          <v-btn
            type="submit"
            color="primary"
            block
            size="large"
            rounded="lg"
            class="login-btn"
            :loading="isLoading"
          >
            <v-icon start>mdi-login</v-icon>
            Accedi
          </v-btn>
        </v-form>
        <p v-if="error" class="text-center text-error mt-4 font-weight-medium">{{ error }}</p>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const username = ref('')
const password = ref('')
const showPassword = ref(false)
const isLoading = ref(false)
const error = ref(null)
const form = ref(null)
const router = useRouter()
const authStore = useAuthStore()

const handleLogin = async () => {
  const { valid } = await form.value.validate()
  if (!valid) return
  isLoading.value = true
  error.value = null
  const ok = await authStore.login(username.value, password.value)
  isLoading.value = false
  if (ok) {
    if (authStore.user?.privilegio === 1) {
      error.value = 'Il tuo account non ha i permessi per accedere al gestionale.'
      await authStore.logout()
      return
    }
    const redirect = router.currentRoute.value.query.redirect
    if (redirect) {
      router.push(redirect)
    } else {
      router.push({ name: 'dashboard' })
    }
  } else {
    error.value = authStore.error || 'Credenziali non valide'
  }
}
</script>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  position: relative;
}

.login-wrapper::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image:
    radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
  pointer-events: none;
}

.login-header {
  background: linear-gradient(135deg, #667eea 0%, #06175d 100%);
}

.login-btn {
  background: linear-gradient(135deg, #667eea 0%, #06175d 100%);
  letter-spacing: 1px;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.login-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(6, 23, 93, 0.4);
}

a:hover {
  text-decoration: underline !important;
}
</style>
