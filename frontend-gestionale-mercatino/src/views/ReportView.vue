<template class="text-center">
  <h1 class="text-center mb-8">Report</h1>

  <div v-if="operazioni.length" class="utenti-container">
    <!-- filtro per cercare per numero libro -->
    <v-text-field
      v-model="ricerca"
      prepend-inner-icon="mdi-magnify"
      label="Cerca per numero libro ..."
      variant="outlined"
      class="text-centered mr-10 ml-10"
    />

    <!-- tabella visualizzazione report -->
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Tipo</th>
          <th>Data</th>
          <th>Numero Libro</th>
          <th>Operatore</th>
          <th>Importo</th>
          <th>Causale</th>
        </tr>
      </thead>
      <tbody>
        <tr :class="op.tipo" v-for="op in logFiltrati" :key="op.id">
          <td>
            <span class="view-span">{{ op.id }}</span>
          </td>
          <td>
            <span class="view-span">{{ op.tipo.charAt(0).toUpperCase() + op.tipo.slice(1) }}</span>
          </td>
          <td>
            <span class="view-span">{{ formatDate(op.data) }}</span>
          </td>
          <td>
            <span class="view-span">{{ op.libro }}</span>
          </td>
          <td>
            <span class="view-span">{{ op.operatore }}</span>
          </td>
          <td>
            <span class="view-span">{{ op.importo }}</span>
          </td>
          <td>
            <span class="view-span">{{ op.causale === null ? '--' : op.causale }}</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div v-else class="text-center mt-4">Caricamento...</div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { apiClient } from '@/apiConfig'

const operazioni = ref([])
const ricerca = ref('')

// Carica i dati dal backend
onMounted(async () => {
  const response = await apiClient.get('/api/operazioni') //mi prendo dal backend le operazioni
  operazioni.value = response.data
})

// computed per filtrare le operazioni in base al numero libro
const logFiltrati = computed(() => {
  const q = ricerca.value?.toString().toLowerCase() || ''
  return operazioni.value.filter((op) => !q || op.libro.toString().toLowerCase().includes(q))
})

// funzione per formattare la data in gg/mm/aaaa
const formatDate = (data) => {
  if (!data) return '--'
  const d = new Date(data)
  return d.toLocaleDateString('it-IT')
}
</script>

<style scoped>
table {
  width: 95%;
  margin: 20px auto 10rem auto;
  margin-bottom: 10rem;
  border-collapse: collapse;
}
th,
td {
  padding: 12px;
  border-bottom: 1px solid #ddd;
  text-align: left;
  overflow: hidden;
}

.ritiro {
  background-color: rgba(0, 128, 0, 0.1); /* verde trasparente */
}
.restituzione {
  background-color: rgba(255, 215, 0, 0.1); /* oro trasparente */
}
.prenotazione {
  background-color: rgba(128, 0, 128, 0.1); /* viola trasparente */
}
.vendita {
  background-color: rgba(0, 0, 255, 0.1); /* blu trasparente */
}
</style>
