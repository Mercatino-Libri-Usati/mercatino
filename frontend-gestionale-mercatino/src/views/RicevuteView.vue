<template>
  <v-container class="ricevute-container py-10 px-12" fluid>
    <h1 class="text-center mb-8">Elenco Ricevute</h1>

    <div class="filters mb-6">
      <v-row class="mb-4">
        <v-col cols="12">
          <div class="d-flex gap-2 flex-wrap justify-center">
            <v-chip-group
              v-model="filtriAttivi.tipo"
              multiple
              filter
              variant="outlined"
              @update:model-value="() => fetchRicevute()"
            >
              <v-chip
                v-for="(info, tipo) in TIPI_RICEVUTE"
                :key="tipo"
                :value="tipo"
                :color="info.color"
                variant="flat"
                label
                :class="{ 'white--text': isChipSelezionato(tipo) }"
                :style="aggiornaStylingChip(tipo)"
              >
                {{ info.label }}
              </v-chip>
            </v-chip-group>
          </div>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" sm="6" md="3">
          <v-text-field
            v-model="filtriAttivi.numero"
            label="Numero Ricevuta"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            @update:model-value="() => fetchRicevute()"
          ></v-text-field>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-text-field
            v-model="filtriAttivi.utente"
            label="Nome Utente"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            @update:model-value="() => fetchRicevute()"
          ></v-text-field>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-text-field
            v-model="filtriAttivi.libro"
            label="Numero Libro"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            @update:model-value="() => fetchRicevute()"
          ></v-text-field>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-text-field
            v-model="filtriAttivi.isbn"
            label="ISBN libro"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            @update:model-value="() => fetchRicevute()"
          ></v-text-field>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-text-field
            v-model="filtriAttivi.date_from"
            label="Data Da"
            type="date"
            variant="outlined"
            density="compact"
            hide-details
            @change="() => fetchRicevute()"
          ></v-text-field>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-text-field
            v-model="filtriAttivi.date_to"
            label="Data A"
            type="date"
            variant="outlined"
            density="compact"
            hide-details
            @change="() => fetchRicevute()"
          ></v-text-field>
        </v-col>
      </v-row>
      <v-btn class="reset-btn" @click="resetFiltri">
        <v-icon left>mdi-filter-remove</v-icon>
      </v-btn>
      <v-btn class="refresh-btn" @click="fetchRicevute()">
        <v-icon left>mdi-refresh</v-icon>
      </v-btn>
      <v-btn @click="espandiTutte()">
        <v-icon left>{{ ricevuteEspanse.size > 0 ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
      </v-btn>
    </div>

    <v-card :loading="loading" class="mb-4">
      <v-table>
        <thead>
          <tr>
            <th style="width: 50px"></th>
            <th class="text-left">Numero</th>
            <th class="text-left">Tipo</th>
            <th class="text-left">Data</th>
            <th class="text-left">Utente</th>
            <th class="text-left">PDF</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="item in ricevute" :key="item.id + '_' + item.tipo">
            <tr :class="{ 'bg-grey-lighten-4': isEspanso(item) }">
              <td>
                <v-btn
                  icon
                  variant="text"
                  size="small"
                  @click="espandiRicevuta(item)"
                  @mouseenter="prefetchDettagliRicevuta(item)"
                >
                  <v-icon>{{ isEspanso(item) ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
                </v-btn>
              </td>
              <td>{{ item.numero }}</td>
              <td>
                <v-chip :color="getColor(item.tipo)" size="small" variant="flat">
                  {{ item.tipo }}
                </v-chip>
              </td>
              <td>{{ formatDate(item.data) }}</td>
              <td>{{ item.nominativo }}</td>
              <td class="d-flex align-center gap-2">
                <v-btn
                  v-if="item.url_pdf"
                  :href="item.url_pdf"
                  target="_blank"
                  variant="text"
                  color="red-darken-1"
                >
                  <v-icon size="xx-large">mdi-file-pdf-box</v-icon>
                </v-btn>
                <span v-else>-</span>
              </td>
            </tr>
            <tr v-if="isEspanso(item)" class="bg-grey-lighten-5">
              <td colspan="5" class="pa-4">
                <div
                  v-if="dettagliInCaricamento[item.id + item.tipo]"
                  class="d-flex justify-center py-2"
                >
                  <v-progress-circular
                    indeterminate
                    size="24"
                    color="primary"
                  ></v-progress-circular>
                </div>
                <div v-else-if="cacheRicevute[item.id + item.tipo]">
                  <v-table density="compact" class="bg-transparent">
                    <thead>
                      <tr>
                        <th>Numero</th>
                        <th>Titolo</th>
                        <th>ISBN</th>
                        <th>Prezzo</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="libro in cacheRicevute[item.id + item.tipo].libri" :key="libro.id">
                        <td>{{ libro.numero_libro }}</td>
                        <td>{{ libro.titolo }}</td>
                        <td>{{ libro.isbn }}</td>
                        <td>{{ libro.prezzo.toFixed(2) }} €</td>
                      </tr>
                    </tbody>
                  </v-table>
                </div>
              </td>
            </tr>
          </template>
          <tr v-if="ricevute.length === 0 && !loading">
            <td colspan="5" class="text-center py-4">Nessuna ricevuta trovata</td>
          </tr>
        </tbody>
      </v-table>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { apiClient } from '@/apiConfig'
import { toast } from '@/toast.js'

const TIPI_RICEVUTE = {
  Ritiro: { label: 'Ritiro', color: 'green', endpoint: 'ritiro' },
  Vendita: { label: 'Vendita', color: 'blue', endpoint: 'vendita' },
  Prenotazione: { label: 'Prenotazione', color: 'purple', endpoint: 'prenotazione' },
  Restituzione: { label: 'Restituzione', color: '#FFD700', endpoint: 'restituzione' },
}

const loading = ref(false)
const ricevute = ref([])
const filtriAttivi = reactive({
  utente: '',
  libro: '',
  isbn: '',
  numero: '',
  tipo: [],
  date_from: '',
  date_to: '',
})
const ricevuteEspanse = ref(new Set())
const cacheRicevute = ref({})
const dettagliInCaricamento = ref({})

const formatDate = (data) =>
  data
    ? new Date(data).toLocaleDateString('it-IT', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      })
    : '-'
const getColor = (tipoRicevuta) => TIPI_RICEVUTE[tipoRicevuta]?.color
const isChipSelezionato = (val) => filtriAttivi.tipo?.includes(val)

const aggiornaStylingChip = (val) => {
  if (isChipSelezionato(val)) return {}
  const color = TIPI_RICEVUTE[val].color
  return { color, border: `1px solid ${color}`, background: 'transparent' }
}

const fetchRicevute = async () => {
  loading.value = true
  try {
    const { data } = await apiClient.get('/api/ricevute', { params: filtriAttivi })
    ricevute.value = data
    // Reset espansioni e cache
    ricevuteEspanse.value = new Set()
    cacheRicevute.value = {}
    dettagliInCaricamento.value = {}
  } catch (err) {
    toast.error('Errore caricamento ricevute: ' + (err.message || err))
  }
  loading.value = false
}

const getKey = (i) => `${i.id}_${i.tipo}`
const isEspanso = (i) => ricevuteEspanse.value.has(getKey(i))

const espandiRicevuta = async (item) => {
  const key = getKey(item)
  if (ricevuteEspanse.value.has(key)) {
    ricevuteEspanse.value.delete(key)
  } else {
    ricevuteEspanse.value.add(key)
    await caricaDettagliRicevuta(item)
  }
}

const espandiTutte = () => {
  if (ricevute.value.length === 0) return
  // Se almeno una è espansa, collassa tutte
  const espanse = Array.from(ricevuteEspanse.value)
  if (espanse.length > 0) {
    ricevuteEspanse.value = new Set()
  } else {
    // Espandi tutte
    ricevuteEspanse.value = new Set(ricevute.value.map(getKey))
    // Prefetch dettagli per tutte
    ricevute.value.forEach((item) => caricaDettagliRicevuta(item))
  }
}

const resetFiltri = () => {
  filtriAttivi.utente = ''
  filtriAttivi.libro = ''
  filtriAttivi.isbn = ''
  filtriAttivi.numero = ''
  filtriAttivi.tipo = []
  filtriAttivi.date_from = ''
  filtriAttivi.date_to = ''
  fetchRicevute()
}

const prefetchDettagliRicevuta = (item) => {
  const key = item.id + item.tipo
  if (!cacheRicevute.value[key] && !dettagliInCaricamento.value[key]) {
    caricaDettagliRicevuta(item)
  }
}

const caricaDettagliRicevuta = async (item) => {
  const key = item.id + item.tipo
  if (cacheRicevute.value[key] || dettagliInCaricamento.value[key]) return
  dettagliInCaricamento.value[key] = true
  try {
    const { data } = await apiClient.get(
      `/api/ricevute/${TIPI_RICEVUTE[item.tipo]?.endpoint}/${item.id}?metadati=false`,
    )
    cacheRicevute.value[key] = data
  } catch (err) {
    toast.error('Errore caricamento dettagli: ' + (err.message || err))
  }
  dettagliInCaricamento.value[key] = false
}

onMounted(fetchRicevute)
</script>
