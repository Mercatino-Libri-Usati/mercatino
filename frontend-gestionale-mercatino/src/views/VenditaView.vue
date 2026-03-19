<template>
  <v-container class="vendita-container py-10 px-12" fluid>
    <h1 class="text-center mb-8">Vendita Libri</h1>

    <div v-if="!ricevutaCreata">
      <!-- Selezione utente -->
      <div class="mb-8">
        <SelectUser v-model="utenteSelezionato" />
      </div>

      <div v-if="store.libriInVendita.length < 20" class="d-flex justify-center mb-12">
        <InputRicerca
          v-model="codiceLibro"
          :loading="loading"
          @search="handleSearch"
          :placeholder="'Inserisci numero libro'"
        />
      </div>

      <div v-if="store.libriInVendita.length > 0">
        <TabellaDinamica
          :items="store.libriInVendita"
          :columns="colonneVendita"
          @remove="store.rimuoviLibro"
          class="mt-16"
        />

        <v-card class="my-4 mx-auto" variant="text" max-width="600">
          <v-card-text class="d-flex align-center justify-center flex-wrap">
            <span class="font-weight-medium">Totale libri: </span>
            <v-chip color="primary" size="small" class="ml-2">
              {{ store.libriInVendita.length }}/20
            </v-chip>
            <span class="font-weight-medium ml-6">Totale Prezzo: </span>
            <v-chip color="green" size="small" class="ml-2">
              {{
                store.libriInVendita
                  .reduce((sum, libro) => sum + parseFloat(libro.prezzo), 0)
                  .toFixed(2)
              }}€
            </v-chip>
          </v-card-text>
        </v-card>

        <div class="d-flex justify-center mt-12">
          <v-btn color="primary" size="large" :loading="loadingVendita" @click="handleConcludi">
            Registra Vendita
          </v-btn>
        </div>
      </div>
    </div>

    <RicevutaSuccess v-else tipo="Vendita" :numero="numeroRicevuta" :pdfUrl="pdfUrl" />
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { apiClient } from '@/apiConfig'
import { useVenditaStore } from '@/stores/venditaStore'
import InputRicerca from '@/components/InputRicerca.vue'
import TabellaDinamica from '@/components/TabellaDinamica.vue'
import RicevutaSuccess from '@/components/RicevutaSuccess.vue'
import SelectUser from '@/components/SelectUser.vue'
import { toast } from '@/toast.js'

const store = useVenditaStore()
const codiceLibro = ref('')
const loading = ref(false)
const loadingVendita = ref(false)
const ricevutaCreata = ref(false)
const numeroRicevuta = ref(null)
const pdfUrl = ref('#')

// Stato per l'utente selezionato
const utenteSelezionato = ref(null)

const colonneVendita = [
  { key: 'isbn', label: 'ISBN', width: '15%', type: 'badge' },
  { key: 'titolo', label: 'Titolo', width: '40%' },
  { key: 'prezzo', label: 'Prezzo (€)', width: '15%', class: 'text-center' },
  { key: 'numerolibro', label: 'Num. Libro', width: '25%' },
]

const handleSearch = async () => {
  const valore = codiceLibro.value?.trim()
  codiceLibro.value = '' // resetta il campo
  if (!valore) return

  loading.value = true
  try {
    await store.aggiungiLibro(valore)
  } catch (err) {
    toast.error(err.response?.data?.message || err)
  } finally {
    loading.value = false
  }
}

const handleConcludi = async () => {
  // VALIDAZIONE: Controlla che l'utente sia stato selezionato
  if (!utenteSelezionato.value) {
    toast.error('Seleziona un utente prima di procedere')
    return
  }

  loadingVendita.value = true
  try {
    const payload = {
      userid: utenteSelezionato.value.id, // usa solo l'ID
      libri: store.libriInVendita.map((libro) => parseInt(libro.id, 10)),
    }

    const { data } = await apiClient.post(`/api/ricevute/vendita`, payload)
    numeroRicevuta.value = data.numero_vendita
    pdfUrl.value = data.pdf_url
    store.svuotaLibri()
    ricevutaCreata.value = true
  } catch (error) {
    toast.error(error || 'Errore vendita')
  } finally {
    loadingVendita.value = false
  }
}
</script>
