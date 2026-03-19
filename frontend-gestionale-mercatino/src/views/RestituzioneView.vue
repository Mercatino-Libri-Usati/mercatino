<script setup>
import { ref, computed, watch } from 'vue'
import { apiClient } from '@/apiConfig'
import { toast } from '@/toast.js'

import RicevutaSuccess from '@/components/RicevutaSuccess.vue'
import TabellaRestituzione from '@/components/TabellaRestituzione.vue'
import SelectUser from '@/components/SelectUser.vue'

const anteprimaData = ref(null)
const loadingRicevuta = ref(false)
const ricevutaCreata = ref(false)
const numeroRicevuta = ref(null)
const pdfUrl = ref(null)

// Stato per l'utente selezionato
const utenteSelezionato = ref(null)

// Calcoli totali
const totaleSoldi = computed(() => {
  return anteprimaData.value?.profitto_utente ?? 0
})

const totaleInvenduti = computed(() => {
  if (!anteprimaData.value?.libri) return 0
  return anteprimaData.value.libri.filter((l) => !l.venduto).length
})

// Caricamento anteprima dinamica in base all'utente selezionato
const caricaAnteprima = async () => {
  if (!utenteSelezionato.value) return
  try {
    const { data } = await apiClient.get(
      `/api/ricevute/restituzione/preview?userid=${utenteSelezionato.value.id}`,
    )
    anteprimaData.value = data
  } catch (err) {
    toast.error(err.response?.data?.message || 'Errore nel caricamento anteprima')
  }
}

// Creazione ricevuta
const creaRicevuta = async () => {
  if (!utenteSelezionato.value) {
    toast.error('Seleziona un utente prima di procedere')
    return
  }

  loadingRicevuta.value = true
  try {
    const { data } = await apiClient.post(`/api/ricevute/restituzione`, {
      userid: utenteSelezionato.value.id,
    })

    numeroRicevuta.value = data.numero_restituzione
    pdfUrl.value = data.pdf_url
    ricevutaCreata.value = true
  } catch (err) {
    toast.error(err.response?.data?.message || 'Errore nella creazione della ricevuta')
  } finally {
    loadingRicevuta.value = false
  }
}

// Ricarica anteprima ogni volta che cambia l'utente selezionato
watch(utenteSelezionato, () => {
  caricaAnteprima()
})
</script>

<template>
  <v-container class="restituzione-container py-10 px-12" fluid>
    <h1 class="text-center mb-8">Restituzione Libri</h1>

    <div v-if="!ricevutaCreata">
      <!-- Selezione utente -->
      <div class="mb-6">
        <SelectUser v-model="utenteSelezionato" />
      </div>

      <div v-if="anteprimaData">
        <!-- Elenco ricevute di ritiro -->
        <v-card class="mb-6 pa-4" variant="text">
          <h3 class="mb-3">Ricevute di Ritiro</h3>
          <div v-if="anteprimaData.ricevute_ritiro && anteprimaData.ricevute_ritiro.length">
            <ul>
              <li v-for="ritiro in anteprimaData.ricevute_ritiro" :key="ritiro">
                Ricevuta n. {{ ritiro }}
              </li>
            </ul>
          </div>
          <div v-else>Nessuna ricevuta di ritiro trovata.</div>
        </v-card>

        <!-- Tabella libri -->
        <v-card class="mb-6 mt-5" variant="text">
          <h3>Elenco Libri da Restituire</h3>
          <TabellaRestituzione :libri="anteprimaData.libri" />
        </v-card>

        <!-- Totali -->
        <v-card class="mb-6 pa-4" variant="text">
          <h3 class="mb-3">Riepilogo</h3>
          <div class="d-flex justify-space-between mb-2">
            <strong>Totale Soldi:</strong>
            <span>€ {{ totaleSoldi.toFixed(2) }}</span>
          </div>
          <div class="d-flex justify-space-between">
            <strong>Libri Invenduti:</strong>
            <span>{{ totaleInvenduti }}</span>
          </div>
        </v-card>

        <!-- Bottone crea ricevuta -->
        <div class="d-flex justify-center mt-8">
          <v-btn
            color="success"
            size="large"
            elevation="2"
            :loading="loadingRicevuta"
            @click="creaRicevuta"
          >
            Crea Ricevuta
          </v-btn>
        </div>
      </div>
    </div>

    <RicevutaSuccess v-else tipo="Restituzione" :numero="numeroRicevuta" :pdf-url="pdfUrl" />
  </v-container>
</template>
