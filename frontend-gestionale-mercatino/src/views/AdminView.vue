<script setup>
import { ref } from 'vue'
import { apiClient } from '@/apiConfig'
import { toast } from '@/toast.js'

const email = ref('')
const privilegio = ref(1)

const modificaPermessi = async () => {
  try {
    await apiClient.post('/api/imposta-privilegi', {
      email: email.value,
      livello: privilegio.value,
    })
    toast.success('Permessi modificati con successo')
  } catch (error) {
    toast.error(error.response?.data?.message || 'Errore durante la modifica dei permessi')
  }
}
</script>

<template>
  <div class="page-container py-10 px-6">
    <div class="text-center mb-8">
      <h1>Amministrazione</h1>
    </div>
    <div>Modifica permessi utente</div>
    <v-row class="mb-4" dense>
      <v-col cols="12" md="6">
        <v-text-field v-model="email" variant="underlined" label="email" />
      </v-col>
      <v-col cols="12" md="6">
        <v-select
          variant="underlined"
          v-model="privilegio"
          :items="[
            { title: 'Utente', value: 1 },
            { title: 'Gestore', value: 2 },
            { title: 'Amministratore', value: 3 },
          ]"
          label="Privilegio"
        />
      </v-col>
    </v-row>
    <v-btn color="primary" @click="modificaPermessi">Modifica permessi</v-btn>

    <v-divider class="my-6 border-opacity-100" />
  </div>
</template>
