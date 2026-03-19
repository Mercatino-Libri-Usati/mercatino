<script setup>
import { ref, onMounted } from 'vue'
import { apiClient } from '@/apiConfig'
import { toast } from '@/toast.js'

const loading = ref(true)
const panels = ref([])
const operazioniPerGiorno = ref([])

const cassaAttuale = ref(0)
const bilancioGiorno = ref(0)
const operazioniOggi = ref([])

// Dialog correzione manuale
const dialogAggiungiCorrezione = ref(false)
const loadingCorr = ref(false)
const formCorr = ref({ importo: '', causale: '' })

const TIPO_CONFIG = {
  ritiro: { label: 'Ritiro', color: 'green', icon: 'mdi-book-arrow-down' },
  vendita: { label: 'Vendita', color: 'blue', icon: 'mdi-cash-register' },
  restituzione: { label: 'Restituzione', color: '#FFD700', icon: 'mdi-book-arrow-up' },
  manuale: { label: 'Manuale', color: 'grey', icon: 'mdi-pencil' },
}

function formatEuro(v) {
  const n = Number(v)
  const sign = n >= 0 ? '+' : ''
  return `${sign}${n.toFixed(2)} €`
}

function euroColor(v) {
  return Number(v) >= 0 ? 'text-green-darken-2' : 'text-red-darken-2'
}

async function caricaDati() {
  loading.value = true
  try {
    const [resCassa, resOperazioni] = await Promise.all([
      apiClient.get('/api/cassa'),
      apiClient.get('/api/operazioni'),
    ])

    cassaAttuale.value = Number(resCassa.data.bilancio)

    // Bilancio del giorno
    const dateOggi = new Date().toISOString().slice(0, 10)
    const entry = resCassa.data.bilancio_giornaliero.find((b) => b.giorno === dateOggi)
    bilancioGiorno.value = entry ? Number(entry.bilancio) : 0

    // Operazioni per giorno e bilanci
    const mappa = {}
    for (const operazione of resOperazioni.data) {
      const giorno = operazione.data.slice(0, 10)
      if (!mappa[giorno]) {
        mappa[giorno] = []
      }
      mappa[giorno].push(operazione)
    }
    const giorni = Object.entries(mappa)
      .sort(([a], [b]) => b.localeCompare(a))
      .map(([giorno, ops]) => {
        const totale = ops.reduce((s, o) => s + Number(o.importo), 0)
        return {
          giorno,
          ops,
          totale,
        }
      })
    operazioniPerGiorno.value = giorni

    // Operazioni oggi
    operazioniOggi.value = resOperazioni.data.filter((o) => o.data.startsWith(dateOggi))
  } catch (e) {
    toast.error('Errore caricamento cassa: ' + (e.message || e))
  } finally {
    loading.value = false
  }
}

async function creaCorrezione() {
  const importoNum = Number(formCorr.value.importo)
  if (isNaN(importoNum) || formCorr.value.importo === '') {
    toast.error('Inserisci un importo valido.')
    return
  }
  loadingCorr.value = true
  try {
    await apiClient.post('/api/operazione/manuale', {
      importo: importoNum,
      causale: formCorr.value.causale || null,
    })
    dialogAggiungiCorrezione.value = false
    formCorr.value = { importo: '', causale: '' }
    await caricaDati()
  } catch (e) {
    toast.error('Errore creazione operazione: ' + (e.response?.data?.message || e.message))
  }
  loadingCorr.value = false
  formCorr.value = { importo: '', causale: '' }
}

async function eliminaOperazione(id) {
  try {
    await apiClient.delete(`/api/operazione/${id}`)
    await caricaDati()
  } catch (e) {
    toast.error('Errore eliminazione operazione: ' + (e.response?.data?.message || e.message))
  }
}

onMounted(caricaDati)
</script>

<template>
  <v-container class="py-10 px-6" fluid>
    <div class="text-center mb-8">
      <h1>Cassa</h1>
    </div>

    <!-- Dialog correzione manuale -->
    <v-dialog v-model="dialogAggiungiCorrezione" max-width="420" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-6 pb-2">
          <v-icon class="mr-2" color="grey-darken-1">mdi-pencil</v-icon>
          Nuova operazione manuale
        </v-card-title>
        <v-card-text class="pa-6 pt-2">
          <v-text-field
            v-model="formCorr.importo"
            label="Importo (€)"
            type="number"
            step="0.01"
            hint="Usa valori negativi per sottrarre dalla cassa"
            persistent-hint
            variant="outlined"
            class="mb-4"
          />
          <v-text-field v-model="formCorr.causale" label="Causale (opzionale)" variant="outlined" />
        </v-card-text>
        <v-card-actions class="pa-6 pt-0 justify-end">
          <v-btn
            color="grey-darken-1"
            :disabled="loadingCorr"
            @click="
              ((dialogAggiungiCorrezione = false),
              (erroreCorr = ''),
              (formCorr = { importo: '', causale: '' }))
            "
          >
            Annulla
          </v-btn>
          <v-btn
            color="blue-darken-1"
            variant="tonal"
            :loading="loadingCorr"
            @click="creaCorrezione"
          >
            Salva
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Chips riepilogo -->
    <v-row justify="center" class="mb-10 gap-y-4">
      <!-- Cassa attuale -->
      <v-col cols="12" sm="4" class="d-flex justify-center">
        <v-card
          rounded="xl"
          elevation="3"
          width="100%"
          max-width="280"
          class="pa-6 text-center"
          :color="cassaAttuale >= 0 ? 'green-lighten-4' : 'red-lighten-4'"
        >
          <v-icon
            size="40"
            :color="cassaAttuale >= 0 ? 'green-darken-2' : 'red-darken-2'"
            class="mb-2"
          >
            mdi-cash
          </v-icon>
          <div class="text-subtitle-1 font-weight-medium text-medium-emphasis mb-1">
            Cassa attuale
          </div>
          <div
            class="text-h5 font-weight-bold"
            :class="cassaAttuale >= 0 ? 'text-green-darken-2' : 'text-red-darken-2'"
          >
            <span v-if="!loading">{{ formatEuro(cassaAttuale) }}</span>
            <v-progress-circular v-else indeterminate size="24" />
          </div>
        </v-card>
      </v-col>

      <!-- Bilancio del giorno -->
      <v-col cols="12" sm="4" class="d-flex justify-center">
        <v-card
          rounded="xl"
          elevation="3"
          width="100%"
          max-width="280"
          class="pa-6 text-center"
          color="blue-lighten-4"
        >
          <v-icon size="40" color="blue-darken-2" class="mb-2"> mdi-calendar-today </v-icon>
          <div class="text-subtitle-1 font-weight-medium text-medium-emphasis mb-1">
            Bilancio oggi
          </div>
          <div class="text-h5 font-weight-bold text-blue-darken-2">
            <span v-if="!loading">{{ formatEuro(bilancioGiorno) }}</span>
            <v-progress-circular v-else indeterminate size="24" />
          </div>
        </v-card>
      </v-col>

      <!-- Numero operazioni del giorno -->
      <v-col cols="12" sm="4" class="d-flex justify-center">
        <v-card
          rounded="xl"
          elevation="3"
          width="100%"
          max-width="280"
          class="pa-6 text-center"
          color="indigo-lighten-4"
        >
          <v-icon size="40" color="indigo-darken-2" class="mb-2">mdi-list-box-outline</v-icon>
          <div class="text-subtitle-1 font-weight-medium text-medium-emphasis mb-1">
            Operazioni oggi
          </div>
          <div class="text-h5 font-weight-bold text-indigo-darken-2">
            <span v-if="!loading">{{ operazioniOggi.length }}</span>
            <v-progress-circular v-else indeterminate size="24" />
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Pulsante nuova correzione -->
    <div v-if="!loading" class="d-flex justify-center mt-4 mb-2">
      <v-btn
        color="blue-darken-1"
        prepend-icon="mdi-pencil-plus"
        @click="dialogAggiungiCorrezione = true"
      >
        Crea correzione
      </v-btn>
    </div>
    <br />

    <!-- Elenco per giorno -->
    <div v-if="loading" class="d-flex justify-center py-8">
      <v-progress-circular indeterminate color="primary" size="48" />
    </div>

    <template v-else>
      <div v-if="operazioniPerGiorno.length === 0" class="text-center text-medium-emphasis py-8">
        Nessuna operazione registrata.
      </div>

      <v-expansion-panels v-else v-model="panels" variant="accordion" multiple>
        <v-expansion-panel
          v-for="giornoObj in operazioniPerGiorno"
          :key="giornoObj.giorno"
          :value="giornoObj.giorno"
        >
          <v-expansion-panel-title expand-icon="mdi-menu-down">
            <div class="d-flex w-100 pr-4">
              <span class="font-weight-bold align-left">{{ giornoObj.giorno }}</span>
              <span class="text-medium-emphasis ml-2"> {{ giornoObj.ops.length }} operazioni </span>
              <span
                class="font-weight-bold text-body-1 ml-auto text-right"
                :class="euroColor(giornoObj.totale)"
              >
                {{ formatEuro(giornoObj.totale) }}
              </span>
            </div>
          </v-expansion-panel-title>

          <v-expansion-panel-text class="pa-0">
            <v-table density="compact">
              <thead>
                <tr>
                  <th class="text-left">Tipo</th>
                  <th class="text-left">Libro</th>
                  <th class="text-left">Causale</th>
                  <th class="text-right">Importo</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="op in giornoObj.ops" :key="op.id">
                  <td>
                    <v-chip
                      :color="TIPO_CONFIG[op.tipo].color"
                      :prepend-icon="TIPO_CONFIG[op.tipo].icon"
                      size="small"
                      variant="tonal"
                    >
                      {{ TIPO_CONFIG[op.tipo].label }}
                    </v-chip>
                  </td>
                  <td class="font-weight-medium">{{ op.libro ?? '-' }}</td>
                  <td class="text-medium-emphasis">{{ op.causale ?? '-' }}</td>
                  <td class="text-right font-weight-bold" :class="euroColor(op.importo)">
                    {{ formatEuro(op.importo) }}
                  </td>
                  <td class="text-center" style="width: 40px">
                    <v-btn
                      v-if="op.tipo === 'manuale'"
                      variant="text"
                      icon="mdi-delete"
                      size="xx-small"
                      color="red-lighten-1"
                      @click="eliminaOperazione(op.id)"
                    />
                  </td>
                </tr>
              </tbody>
            </v-table>
          </v-expansion-panel-text>
        </v-expansion-panel>
      </v-expansion-panels>
    </template>
  </v-container>
</template>
