<template>
  <v-card flat class="page-container mt-4" title="Magazzino">
    <v-text-field
      v-model="ricerca"
      prepend-inner-icon="mdi-magnify"
      label="Cerca titolo, ISBN, proprietario..."
      variant="outlined"
      hide-details
      class="mb-4"
    />

    <v-data-table
      :headers="headers"
      :items="libri"
      :loading="loading"
      :items-per-page="100"
      :search="ricerca"
      density="compact"
      class="table-magazzino"
    >
      <template #[`item.isbn`]="{ item }">
        <span class="text-primary font-weight-medium">{{ item.isbn }}</span>
      </template>

      <template #[`item.titolo`]="{ item }">
        <div class="text-truncate" style="max-width: 250px" :title="item.titolo">
          {{ item.titolo }}
        </div>
      </template>

      <template #[`item.prezzo`]="{ item }">
        <v-text-field
          v-model="item.prezzo"
          type="number"
          variant="outlined"
          density="compact"
          hide-details
        />
      </template>

      <template #[`item.id_ritiro`]="{ item }">
        <v-chip
          v-if="item.id_ritiro"
          size="x-small"
          color="green"
          variant="tonal"
          :to="{ path: '/ricevute', query: { numero: item.id_ritiro, tipo: 'Ritiro' } }"
          >#{{ item.id_ritiro }}</v-chip
        >
        <span v-else class="text-disabled">--</span>
      </template>

      <template #[`item.id_prenotazione`]="{ item }">
        <v-chip
          v-if="item.id_prenotazione"
          size="x-small"
          color="purple"
          variant="tonal"
          :to="{
            path: '/ricevute',
            query: { numero: item.id_prenotazione, tipo: 'Prenotazione' },
          }"
          >#{{ item.id_prenotazione }}</v-chip
        >
        <span v-else class="text-disabled">--</span>
      </template>

      <template #[`item.id_vendita`]="{ item }">
        <v-chip
          v-if="item.id_vendita"
          size="x-small"
          color="blue"
          variant="tonal"
          :to="{ path: '/ricevute', query: { numero: item.id_vendita, tipo: 'Vendita' } }"
          >#{{ item.id_vendita }}</v-chip
        >
        <span v-else class="text-disabled">--</span>
      </template>

      <template #[`item.id_restituzione`]="{ item }">
        <v-chip
          v-if="item.id_restituzione"
          size="x-small"
          color="amber-darken-2"
          variant="tonal"
          :to="{
            path: '/ricevute',
            query: { numero: item.id_restituzione, tipo: 'Restituzione' },
          }"
          >#{{ item.id_restituzione }}</v-chip
        >
        <span v-else class="text-disabled">--</span>
      </template>

      <template #[`item.note`]="{ item }">
        <v-text-field v-model="item.note" variant="outlined" density="compact" hide-details />
      </template>

      <template #[`item.stato`]="{ item }">
        <v-tooltip location="top">
          <template #activator="{ props }">
            <v-icon
              v-bind="props"
              :color="statoConfig[item.stato?.toLowerCase()]?.color || 'grey'"
              :icon="statoConfig[item.stato?.toLowerCase()]?.icon || 'mdi-help-circle-outline'"
              size="small"
            ></v-icon>
          </template>
          <span>{{ item.stato }}</span>
        </v-tooltip>
      </template>

      <template #[`item.azioni`]="{ item }">
        <v-btn
          icon
          size="x-small"
          color="primary"
          :loading="loadingAcquisti.has(item.id)"
          @click="salvaLibro(item)"
        >
          <v-icon>mdi-content-save</v-icon>
        </v-btn>
      </template>

      <template #no-data>
        <div class="py-8 text-center text-disabled">
          <v-icon icon="mdi-book-off-outline" size="40" /><br />Nessun libro trovato
        </div>
      </template>
    </v-data-table>
  </v-card>
</template>

<script setup>
import { shallowRef, ref, onMounted, reactive } from 'vue'
import { apiClient } from '@/apiConfig'
import { toast } from '@/toast.js'

const libri = shallowRef([])
const loading = ref(false)
const loadingAcquisti = reactive(new Set())
const ricerca = ref('')

const headers = [
  { title: 'N', key: 'numero', align: 'center', width: '4%' },
  { title: 'ISBN', key: 'isbn', align: 'center', width: '10%' },
  { title: 'Titolo', key: 'titolo', width: '10%' },
  { title: 'Prezzo', key: 'prezzo', width: '10%', sortable: true },
  { title: 'Ritiro', key: 'id_ritiro', align: 'center', width: '7%' },
  { title: 'Prenotaz.', key: 'id_prenotazione', align: 'center', width: '7%' },
  { title: 'Vendita', key: 'id_vendita', align: 'center', width: '7%' },
  { title: 'Restit.', key: 'id_restituzione', align: 'center', width: '7%' },
  { title: 'Note', key: 'note', sortable: false, width: '10%' },
  { title: 'Proprietario', key: 'proprietario', align: 'center', width: '10%' },
  { title: 'Stato', key: 'stato', align: 'center', width: '4%' },
  { title: 'Azione', key: 'azioni', sortable: false, align: 'center', width: '5%' },
]

const statoConfig = {
  disponibile: { color: 'green', icon: 'mdi-check-circle-outline' },
  venduto: { color: 'blue', icon: 'mdi-cash-check' },
  prenotato: { color: 'purple', icon: 'mdi-bookmark-check' },
  restituito: { color: 'amber-darken-2', icon: 'mdi-keyboard-return' },
}

const caricaLibri = async () => {
  loading.value = true
  try {
    libri.value = (await apiClient.get('/api/libri')).data
  } catch (err) {
    toast.error(err.response?.data?.message || 'Errore caricamento libri')
  } finally {
    loading.value = false
  }
}

// Unico metodo per salvare prezzo e note
const salvaLibro = async (libro) => {
  loadingAcquisti.add(libro.id)
  try {
    await Promise.all([
      apiClient.patch(`/api/libro/${libro.id}/prezzo`, { prezzo: libro.prezzo }),
      apiClient.patch(`/api/libro/${libro.id}/note`, { note: libro.note }),
    ])
    toast.success('Libro aggiornato!')
  } catch (err) {
    toast.error(err.response?.data?.message || 'Errore aggiornamento libro')
  } finally {
    loadingAcquisti.delete(libro.id)
  }
  caricaLibri()
}
onMounted(caricaLibri)
</script>

<style scoped>
.table-magazzino :deep(.v-data-table__th),
.table-magazzino :deep(.v-data-table__td) {
  padding: 0 4px !important;
  font-size: 0.85rem;
}

.titolo-cell {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 500;
}
</style>
