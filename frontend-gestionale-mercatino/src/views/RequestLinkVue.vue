<template>
  <div class="d-flex justify-center align-center" style="min-height: 100vh">
    <v-card width="400" class="pa-6" v-if="!inviato && !loading">
      <v-card-title class="text-center mb-6">Imposta nuova password</v-card-title>

      <v-form @submit.prevent="requestLink">
        <v-text-field v-model="form.email" label="Email" type="email" required class="mb-4" />

        <v-btn type="submit" color="primary" block> Richiedi link </v-btn>
      </v-form>

      <div class="text-center mt-4">
        <router-link to="/login">Torna al Login</router-link>
      </div>
    </v-card>
    <v-card width="400" class="pa-6 text-center" :loading="loading" v-if="loading && !inviato">
      <v-card-title class="mb-4">Invio in corso...</v-card-title>
      <p>Stiamo inviando il link alla tua email. Attendi qualche istante.</p>
    </v-card>
    <v-card width="400" class="pa-6 text-center" v-if ="inviato && !loading">
      <v-card-title class="mb-4">Link inviato!</v-card-title>
      <p>Riceverai a breve un link per impostare una nuova password alla email indicata.</p>
    </v-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { toast } from '@/toast.js'
import { apiClient } from '@/apiConfig'

const form = ref({
  email: '',
})

const loading = ref(false)
const inviato = ref(false)
const route = useRoute()

const requestLink = async () => {
  loading.value = true
  try {
    await apiClient.post('/api/richiedi-link-password', form.value)
    inviato.value = true
  } catch {
    toast.error("Errore durante la richiesta del link. Verifica l'email inserita e riprova.")
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  const emailParam = route.query.email
  if (emailParam && typeof emailParam === 'string') {
    form.value.email = emailParam
    requestLink()
  }
})
</script>
