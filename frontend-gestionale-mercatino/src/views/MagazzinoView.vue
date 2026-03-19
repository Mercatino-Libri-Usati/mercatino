<template>
  <v-container fluid class="py-6 px-4">
    <!-- Header -->
    <div class="d-flex align-center mb-5">
      <h1 class="text-center mb-8">Magazzino</h1>
      <v-spacer />
      <v-chip color="primary" variant="tonal" prepend-icon="mdi-book-multiple"
        >{{ libriFiltrati.length }} libri</v-chip
      >
      <v-btn
        icon="mdi-refresh"
        variant="tonal"
        color="primary"
        size="small"
        :loading="loading"
        @click="caricaLibri"
      />
    </div>

    <v-text-field
      v-model="ricerca"
      prepend-inner-icon="mdi-magnify"
      label="Cerca titolo, ISBN, proprietario..."
      variant="outlined"
      class="text-centered"
    />

    <!-- Tabella -->
    <v-table hover>
      <thead>
        <tr>
          <th class="text-center">
            N
            <v-btn icon size="small" @click="ordinaPerNumero">
              <v-icon size="x-small">mdi-sort</v-icon>
            </v-btn>
          </th>
          <th>ISBN</th>
          <th>Titolo</th>
          <th>Prezzo</th>
          <th>Ritiro</th>
          <th>Prenotaz.</th>
          <th>Vendita</th>
          <th>Restit.</th>
          <th>Note</th>
          <th>Proprietario</th>
          <th>Stato</th>
          <th>Azioni</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="12" class="text-center py-8">
            <v-progress-circular indeterminate color="primary" />
          </td>
        </tr>
        <tr v-else-if="!libriFiltrati.length">
          <td colspan="12" class="text-center py-8 text-disabled">
            <v-icon icon="mdi-book-off-outline" size="40" /><br />Nessun libro trovato
          </td>
        </tr>
        <tr v-for="l in libriFiltrati" :key="l.id">
          <td class="text-center">{{ l.numero }}</td>
          <td class="text-center text-primary font-weight-medium">{{ l.isbn }}</td>
          <td class="titolo-cell">{{ l.titolo }}</td>
          <td>
            <v-text-field
              v-model="l.prezzo"
              type="number"
              variant="outlined"
              density="compact"
              hide-details
              prefix="€"
              style="width: 150px"
            />
          </td>
          <td class="text-center">
            <v-chip v-if="l.id_ritiro" size="x-small" color="green" variant="tonal"
              >#{{ l.id_ritiro }}</v-chip
            ><span v-else class="text-disabled">--</span>
          </td>
          <td class="text-center">
            <v-chip v-if="l.id_prenotazione" size="x-small" color="purple" variant="tonal"
              >#{{ l.id_prenotazione }}</v-chip
            ><span v-else class="text-disabled">--</span>
          </td>
          <td class="text-center">
            <v-chip v-if="l.id_vendita" size="x-small" color="blue" variant="tonal"
              >#{{ l.id_vendita }}</v-chip
            ><span v-else class="text-disabled">--</span>
          </td>
          <td class="text-center">
            <v-chip v-if="l.id_restituzione" size="x-small" color="gold" variant="tonal"
              >#{{ l.id_restituzione }}</v-chip
            ><span v-else class="text-disabled">--</span>
          </td>
          <td>
            <v-text-field
              v-model="l.note"
              variant="outlined"
              density="compact"
              hide-details
              style="width: 130px"
            />
          </td>
          <td class="text-center text-body-2">{{ l.proprietario }}</td>
          <td class="text-center">
            <v-chip
              :color="statoColor[l.stato?.toLowerCase()] || 'grey'"
              :prepend-icon="statoIcon[l.stato?.toLowerCase()] || 'mdi-help-circle-outline'"
              size="small"
              variant="tonal"
              >{{ l.stato }}</v-chip
            >
          </td>
          <td>
            <v-btn icon size="x-small" color="primary" :loading="l._loading" @click="salvaLibro(l)">
              <v-icon>mdi-content-save</v-icon>
            </v-btn>
          </td>
        </tr>
      </tbody>
    </v-table>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { apiClient } from '@/apiConfig'
const libri = ref([])
const loading = ref(false)
const ricerca = ref('')
const filtroStato = ref(null)
import { toast } from '@/toast.js'

const statoColor = {
  disponibile: 'green',
  venduto: 'blue',
  prenotato: 'purple',
  restituito: 'amber-darken-2',
  ritirato: 'purple',
}
const statoIcon = {
  disponibile: 'mdi-check-circle-outline',
  venduto: 'mdi-cash-check',
  prenotato: 'mdi-bookmark-check',
  restituito: 'mdi-keyboard-return',
  ritirato: 'mdi-truck-check',
}

const caricaLibri = async () => {
  loading.value = true
  try {
    libri.value = (await apiClient.get('/api/libri')).data
  } catch (err) {
    toast.error('Errore caricamento libri: ' + (err.message || err))
  } finally {
    loading.value = false
  }
}
const ordineCrescente = ref(true)

const ordinaPerNumero = () => {
  libri.value.sort((a, b) => (ordineCrescente.value ? a.numero - b.numero : b.numero - a.numero))
  ordineCrescente.value = !ordineCrescente.value
}

// Unico metodo per salvare prezzo e note
const salvaLibro = async (l) => {
  l._loading = true
  try {
    await Promise.all([
      apiClient.patch(`/api/libro/${l.id}/prezzo`, { prezzo: l.prezzo }),
      apiClient.patch(`/api/libro/${l.id}/note`, { note: l.note }),
    ])
    toast.success('Libro aggiornato!')
  } catch (err) {
    toast.error('Errore aggiornamento libro: ' + (err.message || err))
  } finally {
    l._loading = false
  }
}

const libriFiltrati = computed(() => {
  const q = ricerca.value?.toLowerCase() || ''
  return libri.value.filter(
    (l) =>
      (!q || [l.titolo, l.isbn, l.proprietario].some((v) => v?.toLowerCase().includes(q))) &&
      (!filtroStato.value || l.stato === filtroStato.value),
  )
})

onMounted(caricaLibri)
</script>

<style scoped>
.titolo-cell {
  max-width: 180px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 500;
}
</style>
