<template>
  <div class="d-flex justify-center align-center" style="min-height: 100vh">
    <v-card width="400" class="pa-6">
      <v-card-title class="text-center mb-6">Imposta nuova password</v-card-title>

      <v-form @submit.prevent="requestLink">
        <v-text-field
          v-model="form.password1"
          label="Nuova password"
          type="password"
          required
          class="mb-4"
        />
        <v-text-field
          v-model="form.password2"
          label="Conferma password"
          type="password"
          required
          class="mb-4"
        />

        <v-btn type="submit" color="primary" block :loading="loading"> Imposta password </v-btn>
      </v-form>
    </v-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { toast } from '@/toast.js'
import { apiClient } from '@/apiConfig'
import Router from '@/router'
import { useAuthStore } from '@/stores/authStore'

const form = ref({
  password1: '',
  password2: '',
  token: '',
  email: '',
})

const loading = ref(false)
const route = useRoute()
const authStore = useAuthStore()

const requestLink = async () => {
  if (form.value.password1 !== form.value.password2) {
    toast.error('Le password non coincidono. Riprova.')
    return
  }

  if (form.value.password1.length < 6) {
    toast.error('La password deve essere lunga almeno 6 caratteri.')
    return
  }
  loading.value = true
  try {
    await apiClient.post('/api/imposta-password', {
      password: form.value.password1,
      token: form.value.token,
    })

    // Login automatico dopo l'impostazione della password
    if (form.value.email) {
      const loginSuccess = await authStore.login(form.value.email, form.value.password1)
      if (loginSuccess) {
        toast.success('Password impostata e login effettuato con successo.')
        Router.push({ name: 'dashboard' })
        return
      }
    }

    // Se qualcosa fallisce nel login automatico (es. no email) andiamo al login
    toast.success('Password impostata con successo. Esegui il login.')
    Router.push('/login')
  } catch {
    toast.error("Errore durante l'impostazione della password. Verifica che il link sia valido.")
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  const token = route.query.token
  const email = route.query.email

  if (token && typeof token === 'string') {
    form.value.token = token
    if (email && typeof email === 'string') {
      form.value.email = email
    }
  } else {
    Router.push('/richiedi-link-password')
  }
})
</script>
