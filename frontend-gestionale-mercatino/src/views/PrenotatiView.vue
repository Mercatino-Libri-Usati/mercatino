<template>
  <br />
  <v-card class="page-container" flat title="Prenotati">
    <v-data-table
      :headers="headers"
      :items="prenotazioni"
      :loading="loading"
      :group-by="[{ key: 'numero_prenotazione' }]"
      item-value="id_libro"
      class="prenotati-table"
    >
      <template v-slot:group-header="{ item, columns, toggleGroup, isGroupOpen }">
        <tr>
          <td :colspan="columns.length">
            <VBtn
              :icon="isGroupOpen(item) ? '$expand' : '$next'"
              size="small"
              variant="text"
              @click="toggleGroup(item)"
            ></VBtn>
            <span class="font-weight-bold"
              >Prenotazione #{{ item.value }} ({{ item.items[0].raw.utente_nome }}
              {{ item.items[0].raw.utente_cognome }})</span
            >
          </td>
        </tr>
      </template>

      <template #[`item.id_libro`]="{ item }">
        <v-chip color="primary" variant="tonal" size="small">
          {{ item.id_libro }}
        </v-chip>
      </template>

      <template #[`item.vendita`]="{ item }">
        <v-chip v-if="item.vendita" color="success" size="small" variant="tonal">
          Vendita #{{ item.vendita }}
        </v-chip>
        <span v-else class="text-caption text-grey">No</span>
      </template>

      <template #[`item.azioni`]="{ item }">
        <div class="d-flex justify-end ga-2">
          <v-btn
            icon="mdi-swap-horizontal"
            size="small"
            variant="tonal"
            color="blue"
            @click="openScambiaModale(item)"
          />
          <v-btn
            icon="mdi-delete-outline"
            size="small"
            variant="tonal"
            color="red"
            @click="eliminaPrenotazione(item.id_libro)"
          ></v-btn>
        </div>
      </template>

      <template #no-data>
        <div class="py-8 text-center text-medium-emphasis">Nessun libro prenotato trovato</div>
      </template>
    </v-data-table>

    <!-- Modale Scambio -->
    <v-dialog v-model="showScambiaModal" max-width="600">
      <v-card>
        <v-card-text class="pt-4">
          <p class="mb-4">
            Seleziona il nuovo libro da scambiare per: <br />
            <strong>{{ libroDaScambiare.titolo }}</strong> (ISBN: {{ libroDaScambiare.isbn }})
          </p>

          <v-data-table
            :headers="headersScambia"
            :items="libriScambiabili"
            :loading="loadingScambiabili"
            hide-default-footer
          >
            <template #[`item.proprietario`]="{ item }">
              {{ item.utente_nome }} {{ item.utente_cognome }}
            </template>
            <template #[`item.azioni`]="{ item }">
              <v-btn color="primary" size="small" @click="confermaScambio(item)"> Seleziona </v-btn>
            </template>
            <template #no-data>
              <div class="py-4 text-center text-medium-emphasis">
                Nessun libro alternativo disponibile per questo ISBN.
              </div>
            </template>
          </v-data-table>
        </v-card-text>
        <v-card-actions class="justify-end px-4 pb-4">
          <v-btn color="red-darken-2" variant="tonal" @click="showScambiaModal = false">
            Annulla
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="dialogConfermaEliminazione" max-width="400">
      <v-card>
        <v-card-title class="text-h6">Conferma Eliminazione</v-card-title>
        <v-card-text> Sei sicuro di voler eliminare questa prenotazione? </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" text @click="dialogConfermaEliminazione = false">Annulla</v-btn>
          <v-btn color="error" @click="confermaEliminazione">Elimina</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { apiClient } from '@/apiConfig'
import { toast } from '@/toast'

const prenotazioni = ref([])
const loading = ref(false)

const showScambiaModal = ref(false)
const libroDaScambiare = ref(null)
const libriScambiabili = ref([])
const loadingScambiabili = ref(false)

const headers = [
  { title: 'N. Libro', key: 'numero_libro' },
  { title: 'Titolo', key: 'titolo' },
  { title: 'ISBN', key: 'isbn' },
  { title: 'N. Prenotazione', key: 'numero_prenotazione' },
  { title: 'Vendita', key: 'vendita' },
  { title: 'Azioni', key: 'azioni', sortable: false, align: 'end' },
]

const headersScambia = [
  { title: 'N. Libro', key: 'numero_libro' },
  { title: 'Proprietario (Ritiro)', key: 'proprietario' },
  { title: 'Note', key: 'note' },
  { title: 'Seleziona', key: 'azioni', sortable: false, align: 'end' },
]

const caricaPrenotazioni = async () => {
  loading.value = true

  try {
    const { data } = await apiClient.get('/api/prenotazioni')
    prenotazioni.value = Array.isArray(data) ? data : []
  } catch (err) {
    toast.error(err.response?.data?.message || 'Errore caricamento prenotazioni.')
    prenotazioni.value = []
  } finally {
    loading.value = false
  }
}

const dialogConfermaEliminazione = ref(false)
const idDaEliminare = ref(null)

const eliminaPrenotazione = (id) => {
  idDaEliminare.value = id
  dialogConfermaEliminazione.value = true
}

const confermaEliminazione = async () => {
  if (!idDaEliminare.value) return

  try {
    await apiClient.post(`/api/prenotazioni/rimuoviLibro`, { id_libro: idDaEliminare.value })
    toast.success('Prenotazione eliminata con successo.')
    dialogConfermaEliminazione.value = false
    caricaPrenotazioni()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Errore eliminazione prenotazione.')
  }
}

const openScambiaModale = async (item) => {
  libroDaScambiare.value = item
  showScambiaModal.value = true
  loadingScambiabili.value = true
  libriScambiabili.value = []

  try {
    const { data } = await apiClient.get(`/api/prenotazioni/libriScambiabili/${item.isbn}`)
    libriScambiabili.value = Array.isArray(data) ? data : []
  } catch (err) {
    toast.error(err.response?.data?.message || 'Errore caricamento libri scambiabili.')
    showScambiaModal.value = false
  } finally {
    loadingScambiabili.value = false
  }
}

const confermaScambio = async (nuovoLibro) => {
  loading.value = true
  showScambiaModal.value = false
  try {
    await apiClient.post('/api/prenotazioni/scambiaLibro', {
      vecchio_id: libroDaScambiare.value.id_libro,
      nuovo_id: nuovoLibro.id,
    })
    toast.success('Libro scambiato con successo.')

    caricaPrenotazioni()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Errore nello scambio del libro.')
  }
}

onMounted(caricaPrenotazioni)
</script>
