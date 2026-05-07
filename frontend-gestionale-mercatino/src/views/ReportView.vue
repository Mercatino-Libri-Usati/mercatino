<template>
  <br />
  <v-card class="page-container" flat title="Report">
    <template v-slot:text>
      <v-text-field
        v-model="ricerca"
        label="Ricerca"
        prepend-inner-icon="mdi-magnify"
        variant="outlined"
        hide-details
        single-line
      ></v-text-field>
    </template>

    <v-data-table
      :headers="headers"
      :items="operazioni"
      :loading="loading"
      :search="ricerca"
      :items-per-page="100"
      :filter-keys="['libro', 'operatore', 'causale', 'tipo', 'data']"
      class="report-table"
      :row-props="rowProps"
    >
      <template #[`item.data`]="{ item }">
        {{ new Date(item.data).toLocaleDateString('it-IT') }}
      </template>
    </v-data-table>
  </v-card>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { apiClient } from '@/apiConfig'

const operazioni = ref([])
const ricerca = ref('')
const loading = ref(false)

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Tipo', key: 'tipo' },
  { title: 'Data', key: 'data' },
  { title: 'Numero Libro', key: 'libro' },
  { title: 'Operatore', key: 'operatore' },
  { title: 'Importo', key: 'importo' },
  { title: 'Causale', key: 'causale' },
]

// Carica i dati dal backend
onMounted(async () => {
  loading.value = true
  try {
    const response = await apiClient.get('/api/operazioni')
    operazioni.value = response.data
  } finally {
    loading.value = false
  }
})

const rowProps = ({ item }) => ({
  class: item?.tipo,
})
</script>

<style scoped>
.report-table :deep(tr.ritiro) {
  background-color: rgba(0, 128, 0, 0.1);
}

.report-table :deep(tr.restituzione) {
  background-color: rgba(255, 215, 0, 0.1);
}

.report-table :deep(tr.vendita) {
  background-color: rgba(0, 42, 255, 0.1);
}
</style>
