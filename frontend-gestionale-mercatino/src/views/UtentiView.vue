<!-- eslint-disable vue/valid-v-slot -->
<template>
  <div class="mt-4">
    <v-card class="page-container" title="Utenti" flat>
      <template v-slot:text>
        <div class="d-flex align-center ga-3 mb-4">
          <v-text-field
            v-model="ricerca"
            label="Ricerca"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            hide-details
            single-line
          ></v-text-field>
          <v-btn
            icon="mdi-account-plus"
            color="primary"
            variant="tonal"
            @click="openAddModal"
            title="Aggiungi utente"
          >
          </v-btn>
        </div>

        <div class="d-flex flex-wrap justify-center ga-1 mb-4">
          <v-btn
            v-for="letter in alfabeto"
            :key="letter"
            @click="letteraSelezionata = letteraSelezionata === letter ? '' : letter"
            :variant="letteraSelezionata === letter ? 'tonal' : 'text'"
            :color="letteraSelezionata === letter ? 'primary' : 'default'"
            size="small"
            min-width="36"
            class="px-2"
          >
            {{ letter }}
          </v-btn>
        </div>
      </template>

      <v-data-table
        :headers="headers"
        :items="filteredByLetter"
        :loading="loading"
        :search="ricerca"
        :items-per-page="100"
        :sort-by="['cognome', 'nome']"
        :filter-keys="['nome', 'cognome', 'email', 'telefono']"
        :row-props="rowProps"
      >
        <template #item.nome="{ item }">
          <input v-if="userStore.editingId === item.id" v-model="item.nome" class="edit-input" />
          <span v-else>{{ item.nome }}</span>
        </template>

        <template #item.cognome="{ item }">
          <input v-if="userStore.editingId === item.id" v-model="item.cognome" class="edit-input" />
          <span v-else>{{ item.cognome }}</span>
        </template>

        <template #item.email="{ item }">
          <input v-if="userStore.editingId === item.id" v-model="item.email" class="edit-input" />
          <span v-else>{{ item.email }}</span>
        </template>

        <template #item.telefono="{ item }">
          <input
            v-if="userStore.editingId === item.id"
            v-model="item.telefono"
            class="edit-input"
          />
          <span v-else>{{ item.telefono }}</span>
        </template>

        <template #item.verificato="{ item }">
          <v-icon
            :icon="item.verificato ? 'mdi-check-circle' : 'mdi-close-circle'"
            :color="item.verificato ? 'green' : 'red'"
          />
        </template>

        <template #item.actions="{ item }">
          <div>
            <v-icon
              v-if="userStore.editingId !== item.id"
              @click="startEdit(item)"
              color="blue"
              class="mr-2"
              title="Modifica"
            >
              mdi-pencil-outline
            </v-icon>

            <v-icon v-else @click="handleSave(item)" color="success" class="mr-2" title="Salva">
              mdi-content-save-outline
            </v-icon>

            <v-icon
              v-if="userStore.editingId !== item.id"
              @click="viewReceipts(item)"
              color="green"
              title="Visualizza ricevute"
            >
              mdi-receipt-text-outline
            </v-icon>

            <v-icon v-else @click="cancelEdit" color="orange" title="Annulla"> mdi-close </v-icon>
          </div>
        </template>
      </v-data-table>

      <v-dialog v-model="isAddModalOpen" max-width="500px">
        <v-card>
          <v-card-title>
            <span class="text-h5">Nuovo Utente</span>
          </v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newUser.nome" label="Nome*" required></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newUser.cognome" label="Cognome*" required></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field v-model="newUser.email" label="Email*" required></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="newUser.telefono"
                    label="Telefono*"
                    required
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="blue-darken-1" variant="text" @click="closeAddModal"> Annulla </v-btn>
            <v-btn color="blue-darken-1" variant="text" @click="saveNewUser"> Salva </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-card>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/usersStore'
import { toast } from '@/toast.js'
import { apiClient } from '@/apiConfig'

const router = useRouter()

const userStore = useUserStore()
const ricerca = ref('')
const letteraSelezionata = ref('')
const alfabeto = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('')
const loading = ref(false)
const isAddModalOpen = ref(false)
const newUser = ref({ nome: '', cognome: '', email: '', telefono: '' })
const rowProps = () => ({})

const filteredByLetter = computed(() => {
  if (!letteraSelezionata.value) {
    return userStore.utenti
  }
  return userStore.utenti.filter((utente) =>
    utente.cognome?.toUpperCase().startsWith(letteraSelezionata.value),
  )
})

const headers = [
  { title: 'ID', value: 'id', width: '60px' },
  { title: 'Cognome', value: 'cognome', sortable: true },
  { title: 'Nome', value: 'nome', sortable: true },
  { title: 'Email', value: 'email', sortable: true },
  { title: 'Telefono', value: 'telefono' },
  { title: 'Verificato', value: 'verificato', align: 'center', width: '100px' },
  { title: 'Azioni', value: 'actions', sortable: false, align: 'center', width: '120px' },
]

onMounted(() => {
  userStore.fetchUtenti()
})

const openAddModal = () => {
  newUser.value = { nome: '', cognome: '', email: '', telefono: '' }
  isAddModalOpen.value = true
}

const closeAddModal = () => {
  isAddModalOpen.value = false
}

const saveNewUser = async () => {
  try {
    await apiClient.post('/api/registra', newUser.value)
    await apiClient.post('/api/richiedi-link-password', { email: newUser.value.email })
    toast.success('Utente registrato con successo.')
    closeAddModal()
    await userStore.fetchUtenti()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Errore durante la registrazione.')
  }
}

const handleSave = async (utente) => {
  if (await userStore.updateUser(utente)) {
    toast.success(`Modifiche effettuate con successo per l'utente ${utente.nome}.`)
  } else {
    toast.error(`Errore durante l'aggiornamento dell'utente ${utente.nome}.`)
  }
  userStore.editingId = null
}

const startEdit = (utente) => {
  userStore.editingId = utente.id
}

const cancelEdit = () => {
  userStore.editingId = null
}

const viewReceipts = (utente) => {
  router.push({
    path: '/ricevute',
    query: { utente: utente.nome + ' ' + utente.cognome },
  })
}
</script>

<style scoped>
.edit-input {
  width: 100%;
  padding: 6px;
  border: 1px solid #1976d2;
  border-radius: 4px;
}
</style>
