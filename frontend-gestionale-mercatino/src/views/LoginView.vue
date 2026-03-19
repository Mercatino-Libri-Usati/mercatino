<template>
  <div class="login-wrapper">
    <div class="login-container">
      <v-card class="login-card">
        <v-card-title class="login-header">
          <v-icon size="32" class="header-icon">mdi-shield-account</v-icon>
          <h2>Accedi al Gestionale</h2>
        </v-card-title>

        <v-card-text class="login-content">
          <p class="welcome-text">Inserisci le tue credenziali per continuare</p>

          <v-form ref="form" @submit.prevent="handleLogin" class="login-form">
            <v-text-field
              v-model="username"
              label="Username"
              prepend-icon="mdi-account-circle"
              placeholder="Il tuo username o la tua email"
              class="mb-4 form-input"
              :rules="[(v) => !!v || 'Username obbligatorio']"
              outlined
              dense
              width="320"
              autocomplete="login"
            />

            <v-text-field
              v-model="password"
              :label="showPassword ? 'Password' : 'Password'"
              :type="showPassword ? 'text' : 'password'"
              prepend-icon="mdi-lock"
              :append-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
              placeholder="La tua password"
              class="mb-6 form-input"
              :rules="[(v) => !!v || 'Password obbligatoria']"
              @click:append="showPassword = !showPassword"
              outlined
              dense
              autocomplete="current-password"
            />

            <v-btn type="submit" color="primary" block class="login-btn" :loading="isLoading">
              <v-icon left>mdi-login</v-icon>
              Accedi
            </v-btn>
          </v-form>
          <p v-if="error" class="status error">{{ error }}</p>
        </v-card-text>
      </v-card>
    </div>
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
    // Se c'è un redirect nella query, vai lì, altrimenti dashboard
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
  background: linear-gradient(135deg, #667eea 0%, #1241ff 50%, #0e051c 100%);
  position: relative;
  overflow: hidden;
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

.login-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  position: relative;
}

.login-card {
  width: 100%;
  max-width: 420px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.95);
}

.login-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  background: linear-gradient(135deg, #667eea 0%, #06175d 100%);
  color: white;
  padding: 32px 24px 24px;
  border-radius: 20px 20px 0 0;
}

.login-content {
  padding: 2.5rem 2rem; /* padding maggiore nel form */
}

.welcome-text {
  text-align: center;
  color: #666;
  margin-bottom: 32px;
  font-size: 16px;
}

.login-form .form-input {
  width: 100%; /* tutti i campi uguali */
  padding: 0.5rem 1rem; /* padding interno maggiore */
}

.login-btn {
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  border-radius: 12px;
  background: linear-gradient(135deg, #667eea 0%, #06175d 100%);
}

.login-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(56, 82, 195, 0.4);
}

.status {
  text-align: center;
  margin-top: 16px;
  font-size: 14px;
}

.error {
  color: #e53935;
}

.status {
  text-align: center;
  margin-top: 16px;
  font-size: 14px;
}

.error {
  color: #e53935;
}
</style>
