<script setup>
import { onMounted, defineAsyncComponent } from 'vue'
import { useCatalogoStore } from '@/stores/catalogoStore'
import { toast } from '@/toast.js'
const RicercaLibro = defineAsyncComponent(() => import('@/components/RicercaLibro.vue'))
const LibroCard = defineAsyncComponent(() => import('@/components/LibroCard.vue'))

const catalogoStore = useCatalogoStore()
const getChipColor = catalogoStore.getChipColorFn

// Modifica prezzo libro
const modificaPrezzo = async (libro) => {
  const input = prompt(`Nuovo prezzo per "${libro.titolo}"`, libro.prezzo)
  if (input === null) return

  // Accetta sia virgola che punto come separatore decimale
  const prezzo = parseFloat(input.replace(',', '.'))
  if (isNaN(prezzo)) {
    toast.error('Prezzo non valido')
    return
  }

  try {
    await catalogoStore.updatePrezzo(libro.id_catalogo, prezzo)
    toast.success('Prezzo aggiornato!')
  } catch (e) {
    toast.error(e.message || 'Errore')
  }
}

onMounted(async () => {
  await catalogoStore.getCatalogo()
})
</script>

<template>
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
          @modifica-prezzo="modificaPrezzo"
        />
      </div>
    </template>
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
