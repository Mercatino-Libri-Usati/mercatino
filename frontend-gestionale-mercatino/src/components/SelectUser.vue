<script setup>
import { ref, onMounted, watch } from 'vue'
import { apiClient } from '@/apiConfig'
import { toast } from '@/toast.js'

// Props standard per v-model
const props = defineProps({
  modelValue: Object, // l'oggetto utente selezionato
})
const emit = defineEmits(['update:modelValue'])

const users = ref([])
const selectedUserId = ref(null)

onMounted(async () => {
  try {
    const { data } = await apiClient.get('/api/elenco-utenti-ridotto')

    // Creiamo un campo nomeCompleto da usare come titolo
    users.value = data.map((u) => ({
      id: u.id,
      nomeCompleto: u.nome_cognome, // campo restituito dall'API
    }))

    // Sincronizza l'ID se c'è già un utente selezionato (viene passato dal genitore)
    if (props.modelValue) {
      selectedUserId.value = props.modelValue.id
    }
  } catch {
    toast.error('Errore nel caricamento utenti')
  }
})

// Aggiorna il genitore quando cambia la selezione
watch(selectedUserId, (newId) => {
  const user = users.value.find((u) => u.id === newId) || null
  emit('update:modelValue', user)
})
</script>

<template>
  <div class="mb-4">
    <v-select
      v-if="users.length"
      v-model="selectedUserId"
      :items="users"
      item-title="nomeCompleto"
      item-value="id"
      label="Seleziona utente"
      variant="outlined"
      clearable
    />
    <v-skeleton-loader v-else type="text" />
  </div>
</template>
