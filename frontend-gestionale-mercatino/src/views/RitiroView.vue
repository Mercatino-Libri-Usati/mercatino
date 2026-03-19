<template>
  <v-container class="ritiro-container py-10 px-12" fluid>
    <h1 class="text-center mb-8">Ritiro Libri</h1>

    <div v-if="!ricevutaCreata">
      <SelectUser v-model="utenteSelezionato" />
    </div>

    <div v-if="!ricevutaCreata">
      <div v-if="store.libri.length < 20" class="d-flex justify-center mb-12">
        <InputRicerca v-model="isbn" :loading="loading" @search="handleSearch" />
      </div>
      <v-alert v-else type="warning" class="mb-16 text-center" prominence>
        Limite massimo raggiunto. Procedi con la registrazione.
      </v-alert>

      <TabellaDinamica
        :items="store.libri"
        :columns="colonneRitiro"
        @remove="store.rimuoviLibro"
        class="mt-15"
      />

      <v-card v-if="store.libri.length > 0" class="my-4 mx-auto" variant="text" max-width="600">
        <v-card-text class="d-flex align-center justify-center flex-wrap">
          <span class="font-weight-medium">Totale libri inseriti: </span>
          <v-chip color="primary" size="small" class="ml-2">{{ store.libri.length }}/20</v-chip>
        </v-card-text>
      </v-card>

      <div class="d-flex justify-center mt-12" v-if="store.libri.length > 0">
        <v-btn
          color="primary"
          size="large"
          elevation="2"
          :loading="loadingRicevuta"
          @click="handleInvia"
        >
          Registra nuovi libri
        </v-btn>
      </div>
    </div>

    <RicevutaSuccess v-else tipo="ritiro" :numero="numeroRicevuta" :pdfUrl="pdfUrl" />
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useRitiroStore } from '@/stores/ritiroStore'
import { apiClient } from '@/apiConfig'
import InputRicerca from '@/components/InputRicerca.vue'
import TabellaDinamica from '@/components/TabellaDinamica.vue'
import RicevutaSuccess from '@/components/RicevutaSuccess.vue'
import { toast } from '@/toast.js'
import SelectUser from '@/components/SelectUser.vue'

const store = useRitiroStore()
const isbn = ref('')
const loading = ref(false)
const loadingRicevuta = ref(false)
const ricevutaCreata = ref(false)
const numeroRicevuta = ref(null)
const pdfUrl = ref('#')

const utenteSelezionato = ref(null)

const colonneRitiro = [
  { key: 'isbn', label: 'ISBN', width: '15%', type: 'badge' },
  { key: 'titolo', label: 'Titolo', width: '35%' },
  {
    key: 'prezzo',
    label: 'Prezzo (€)',
    width: '10%',
    type: 'input',
    inputType: 'number',
    centered: true,
  },
  { key: 'note', label: 'Note', width: '35%', type: 'input', placeholder: 'Aggiungi nota...' },
]

const handleSearch = async () => {
  const valore = isbn.value?.trim()
  if (!valore) return

  loading.value = true
  try {
    await store.aggiungiLibro(valore)
    isbn.value = ''
  } catch (err) {
    toast.error(err.response?.data?.message || 'Libro non valido')
  } finally {
    loading.value = false
  }
}

const handleInvia = async () => {
  // Controlliamo che l'utente sia stato scelto
  if (!utenteSelezionato.value) {
    toast.error('Seleziona un utente prima di procedere')
    return
  }

  loadingRicevuta.value = true
  try {
    const payload = {
      // Usiamo l'ID dell'utente selezionato dinamicamente
      userid: utenteSelezionato.value.id,
      libri: store.libri.map((libro) => ({
        isbn: libro.isbn,
        prezzo: libro.prezzo,
        note: libro.note,
      })),
    }
    const { data } = await apiClient.post(`/api/ricevute/ritiro`, payload)

    numeroRicevuta.value = data.numero_ritiro
    pdfUrl.value = data.pdf_url
    store.svuotaLibri()
    ricevutaCreata.value = true
  } catch (err) {
    toast.error(err.response?.data?.message || 'Errore registrazione')
  } finally {
    loadingRicevuta.value = false
  }
}
</script>
