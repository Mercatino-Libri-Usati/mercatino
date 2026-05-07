<script setup>
import { ref, onMounted, computed } from 'vue'
import { apiClient } from '@/apiConfig'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const stats = ref(null)

// Schema dati centralizzato
const statsSchema = computed(() => [
  {
    label: 'Libri totali',
    value: stats.value?.n_libri,
    icon: 'mdi-book-open-page-variant',
    color: 'blue',
  },
  {
    label: 'Libri venduti',
    value: stats.value?.venduti,
    icon: 'mdi-check-decagram-outline',
    color: 'green',
  },
  {
    label: 'Utenti',
    value: stats.value?.n_utenti,
    icon: 'mdi-account-multiple-outline',
    color: 'indigo',
  },
  { label: 'Profitto', value: `${stats.value?.profitto} €`, icon: 'mdi-cash-minus', color: 'red' },
  { label: 'Ricavo', value: `${stats.value?.ricavo} €`, icon: 'mdi-cash-plus', color: 'green' },
])

onMounted(async () => {
  try {
    const res = await apiClient.get('/api/stats')
    stats.value = res.data
  } catch (error) {
    console.error('Errore:', error)
  }
})
</script>

<template>
  <v-container>
    <v-row v-if="!stats" justify="center" class="mt-10">
      <v-progress-circular indeterminate color="primary" size="64" />
    </v-row>

    <v-card v-else class="mx-auto mt-4 pa-6" flat max-width="600">
      <div class="text-center mb-8">
        <h2 class="text-h4 font-weight-bold mb-4">Benvenuto, {{ authStore.user?.nome_cognome }}</h2>
        <v-btn to="/tutorial" color="primary" variant="outlined" rounded>Vai al tutorial</v-btn>
      </div>

      <v-list bg-color="transparent">
        <template v-for="(item, index) in statsSchema" :key="index">
          <v-list-item :title="item.label">
            <template v-slot:prepend>
              <v-icon :icon="item.icon" :color="item.color" size="32" class="mr-4" />
            </template>
            <template v-slot:append>
              <span class="text-h6 font-weight-bold">{{ item.value }}</span>
            </template>
          </v-list-item>
          <v-divider v-if="index < statsSchema.length - 1" />
        </template>
      </v-list>
    </v-card>
  </v-container>
</template>
