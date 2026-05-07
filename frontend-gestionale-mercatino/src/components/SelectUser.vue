<script setup>
import { ref, onMounted, computed } from 'vue'
import { apiClient } from '@/apiConfig'
import { toast } from '@/toast.js'

// Props standard per v-model
const props = defineProps({
  modelValue: Object, // l'oggetto utente selezionato
})
const emit = defineEmits(['update:modelValue'])

const users = ref([])
const loading = ref(false)
const dialog = ref(false)
const search = ref('')

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await apiClient.get('/api/elenco-utenti-ridotto')
    // Creiamo un campo nomeCompleto da usare come titolo nella tabella
    users.value = data.map((u) => ({
      id: u.id,
      nomeCompleto: u.nome_cognome, // campo restituito dall'API
    }))
  } catch (err) {
    toast.error(err.response?.data?.message || 'Errore nel caricamento utenti')
  } finally {
    loading.value = false
  }
})

// Calcola cosa mostrare nel text-field esterno
const displayValue = computed(() => {
  if (!props.modelValue) return ''
  const user = users.value.find((u) => u.id === props.modelValue.id)
  if (user) return user.nomeCompleto
  if (props.modelValue.nomeCompleto) return props.modelValue.nomeCompleto
  if (props.modelValue.nome_cognome) return props.modelValue.nome_cognome
  return `Utente ID: ${props.modelValue.id}`
})

const headers = [
  { title: 'ID', value: 'id', width: '80px' },
  { title: 'Nome e Cognome', value: 'nomeCompleto', sortable: true },
]

const selectUser = (event, { item }) => {
  emit('update:modelValue', item)
  dialog.value = false
  search.value = ''
}

const clearSelection = () => {
  emit('update:modelValue', null)
}
</script>

<template>
  <div class="mb-4">
    <!-- Trigger per aprire la modale -->
    <v-text-field
      :model-value="displayValue"
      label="Clicca per selezionare un utente"
      placeholder="Clicca per cercare e selezionare un utente"
      variant="outlined"
      readonly
      append-inner-icon="mdi-account-search"
      clearable
      @click="dialog = true"
      @click:clear="clearSelection"
      @click:append-inner="dialog = true"
      :loading="loading"
      hide-details
    ></v-text-field>

    <!-- Modale per la ricerca dell'utente -->
    <v-dialog v-model="dialog" max-width="600px">
      <v-card>
        <v-card-title class="d-flex align-center justify-space-between mt-2">
          <span>Ricerca Utente</span>
          <v-btn icon="mdi-close" variant="text" size="small" @click="dialog = false"></v-btn>
        </v-card-title>

        <v-card-text>
          <div class="search-bar mb-4">
            <v-text-field
              v-model="search"
              label="Cerca per Nome, Cognome o ID..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              hide-details
              single-line
              autofocus
            ></v-text-field>
          </div>

          <v-data-table
            :headers="headers"
            :items="users"
            :search="search"
            :loading="loading"
            :items-per-page="10"
            hover
            @click:row="selectUser"
            class="row-pointer"
          >
            <template #no-data> Nessun utente trovato </template>
          </v-data-table>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>
