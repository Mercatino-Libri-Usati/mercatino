<template>
  <div class="d-flex justify-center align-center" style="min-height: 100vh">
    <v-card width="400" class="pa-6">
      <v-card-title class="text-center mb-6">Registrati</v-card-title>

      <v-form @submit.prevent="register">
        <v-text-field v-model="form.nome" label="Nome" required class="mb-4" />

        <v-text-field v-model="form.cognome" label="Cognome" required class="mb-4" />

        <v-text-field v-model="form.email" label="Email" type="email" required class="mb-4" />

        <v-text-field v-model="form.telefono" label="Telefono" required class="mb-6" />

        <v-btn type="submit" color="primary" block :loading="loading"> Registrati </v-btn>
      </v-form>

      <div class="text-center mt-4">
        <router-link to="/login">Torna al Login</router-link>
      </div>
    </v-card>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { toast } from '@/toast.js'
import { apiClient } from '@/apiConfig'
import Router from '@/router'

const form = ref({
  nome: '',
  cognome: '',
  email: '',
  telefono: '',
})

const loading = ref(false)

const register = async () => {
  loading.value = true

  try {
    await apiClient.post('/api/registra', form.value)
    Router.push('/richiedi-link-password?email=' + encodeURIComponent(form.value.email))
  } catch {
    toast.error('Errore durante la registrazione')
  } finally {
    loading.value = false
  }
}
</script>
