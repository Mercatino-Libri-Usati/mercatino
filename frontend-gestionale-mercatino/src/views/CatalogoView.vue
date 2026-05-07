<script setup>
import { onMounted, defineAsyncComponent, ref } from 'vue'
import { useCatalogoStore } from '@/stores/catalogoStore'
import { apiClient } from '@/apiConfig'
import { toast } from '@/toast.js'
const RicercaLibro = defineAsyncComponent(() => import('@/components/RicercaLibro.vue'))
const LibroCard = defineAsyncComponent(() => import('@/components/LibroCard.vue'))

const catalogoStore = useCatalogoStore()
const getChipColor = catalogoStore.getChipColorFn

const dialogPrezzo = ref(false)
const libroDaModificare = ref(null)
const inputPrezzo = ref('')
const loadingPrezzo = ref(false)

const openModificaPrezzo = (libro) => {
  libroDaModificare.value = libro
  inputPrezzo.value = libro.prezzo
  dialogPrezzo.value = true
}

const salvaNuovoPrezzo = async () => {
  if (!libroDaModificare.value) return

  const prezzoNum = parseFloat(String(inputPrezzo.value).replace(',', '.'))
  if (isNaN(prezzoNum)) {
    toast.error('Prezzo non valido')
    return
  }

  loadingPrezzo.value = true
  const id_catalogo = libroDaModificare.value.id_catalogo
  try {
    await apiClient.patch(`/api/catalogo/${id_catalogo}/prezzo`, { prezzo: prezzoNum })

    // Aggiornamento dello store manuale (semplificato)
    const updateLocally = (arr) => {
      const target = arr.find((l) => l.id_catalogo === id_catalogo)
      if (target) target.prezzo = prezzoNum
    }
    updateLocally(catalogoStore.catalogo)
    updateLocally(catalogoStore.libriFiltrati)

    toast.success('Prezzo aggiornato!')
    dialogPrezzo.value = false
  } catch (e) {
    toast.error(e.response?.data?.message || 'Errore aggiornamento prezzo')
  } finally {
    loadingPrezzo.value = false
  }
}

onMounted(async () => {
  await catalogoStore.getCatalogo()
})
</script>

<template>
  <div class="page-container">
    <h1 class="text-center mb-8 mt-4">Catalogo</h1>

    <div class="catalogo">
      <!-- Error -->
      <p v-if="catalogoStore.error" class="status error">{{ catalogoStore.error }}</p>

      <!-- Loading -->
      <div v-if="catalogoStore.loading" class="loading-wrapper">
        <div class="spinner" />
        <p class="loading-text">Caricamento catalogo...</p>
      </div>

      <template v-else>
        <!-- Ricerca -->
        <ricerca-libro />

        <!-- Nessun risultato -->
        <div
          v-if="catalogoStore.catalogo.length > 0 && catalogoStore.getLibriDaMostrare.length === 0"
          class="no-results"
        >
          Nessun libro trovato per la tua ricerca.
        </div>

        <!-- Lista libri -->
        <div v-else class="lista">
          <libro-card
            v-for="libro in catalogoStore.getLibriDaMostrare"
            :key="libro.id_catalogo"
            :libro="libro"
            :get-chip-color="getChipColor"
            @modifica-prezzo="openModificaPrezzo"
          />
        </div>
      </template>
    </div>

    <!-- Modale Modifica Prezzo -->
    <v-dialog v-model="dialogPrezzo" max-width="400px">
      <v-card>
        <v-card-title class="text-h6"> Modifica Prezzo </v-card-title>
        <v-card-text>
          <div v-if="libroDaModificare" class="mb-4">
            Stai modificando il prezzo per:<br />
            <strong>{{ libroDaModificare.titolo }}</strong>
          </div>
          <v-text-field
            v-model="inputPrezzo"
            label="Nuovo Prezzo (€)"
            type="number"
            step="0.10"
            variant="outlined"
            @keyup.enter="salvaNuovoPrezzo"
          ></v-text-field>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" text @click="dialogPrezzo = false">Annulla</v-btn>
          <v-btn color="primary" :loading="loadingPrezzo" @click="salvaNuovoPrezzo">Salva</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<style scoped>
.catalogo {
  max-width: 100%;
  padding: 16px 24px;
}

.status {
  text-align: center;
  padding: 20px;
}

.status.error {
  color: #d32f2f;
}

.no-results {
  text-align: center;
  padding: 30px;
  background: #fff3cd;
  border: 1px solid #ffc107;
  border-radius: 8px;
  color: #856404;
  margin-top: 20px;
}

.loading-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 60px 20px;
  gap: 16px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e0e0e0;
  border-top-color: #1976d2;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.loading-text {
  color: #666;
  font-size: 0.95rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.lista {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
</style>
