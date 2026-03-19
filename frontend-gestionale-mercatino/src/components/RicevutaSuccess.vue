<template>
  <div class="text-center mt-12">
    <h2 class="mb-6">
      Creata ricevuta di {{ props.tipo }}
      <span v-if="props.numero">numero {{ props.numero }}</span>
      creata con successo!
    </h2>
    <div v-if="blobUrl">
      <!-- Visualizza il PDF incorporato, dimensioni di un A4 in orizzontale -->
      <div class="mb-4">
        <embed
          :src="blobUrl"
          type="application/pdf"
          style="display: block; margin: 0 auto; width: 100vw; max-width: 1123px; height: 794px"
        />
        <!-- Fallback per browser senza supporto embed -->
        <noscript>
          <iframe
            :src="blobUrl"
            class="w-full border border-gray-300 rounded"
            style="height: 794px"
          ></iframe>
        </noscript>
      </div>
    </div>
    <div v-else-if="props.pdfUrl && props.pdfUrl !== '#'" class="mb-4">
      Caricamento anteprima...
    </div>
    <div>
      <v-btn
        color="primary"
        size="large"
        prepend-icon="mdi-file-pdf-box"
        :href="blobUrl"
        target="_blank"
        :disabled="!blobUrl"
      >
        Apri file da stampare
      </v-btn>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { apiClient } from '@/apiConfig'

const props = defineProps({
  tipo: {
    type: String,
    required: true,
  },
  numero: {
    type: [String, Number],
    default: null,
  },
  pdfUrl: {
    type: String,
    default: '#',
  },
})

const blobUrl = ref(null)

const fetchPdf = async () => {
  if (!props.pdfUrl || props.pdfUrl === '#') {
    blobUrl.value = null
    return
  }

  try {
    const response = await apiClient.get(props.pdfUrl, {
      responseType: 'blob',
    })
    if (blobUrl.value) {
      URL.revokeObjectURL(blobUrl.value)
    }
    blobUrl.value = URL.createObjectURL(response.data)
  } catch (error) {
    console.error('Errore nel caricamento del PDF:', error)
  }
}

watch(() => props.pdfUrl, fetchPdf)

onMounted(fetchPdf)

onUnmounted(() => {
  if (blobUrl.value) {
    URL.revokeObjectURL(blobUrl.value)
  }
})
</script>
