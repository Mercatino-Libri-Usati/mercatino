<script setup>
import { onMounted } from 'vue'
import { useUserStore } from '@/stores/usersStore'
import RicercaUtente from '@/components/RicercaUtente.vue'
import { toast } from '@/toast.js'

const userStore = useUserStore()

onMounted(() => {
  userStore.fetchUtenti()
})

const handleSave = async (utente) => {
  if (confirm(`Confermi le modifiche per l'utente ${utente.nome}?`)) {
    if (await userStore.updateUser(utente)) {
      toast.success(`Modifiche effettuate con successo per l'utente ${utente.nome}.`)
    } else {
      toast.error(`Errore durante l'aggiornamento dell'utente ${utente.nome}.`)
    }
  } else {
    userStore.editingId = null
  }
  userStore.editingId = null
}
</script>

<template>
  <h1 class="text-center mb-8">Utenti</h1>
  <div v-if="userStore.error">{{ userStore.error }}</div>
  <div v-else class="utenti-container">
    <div class="add-user-container">
      <v-icon class="add-user-btn mt-5 mx-6" @click="userStore.addUser()">mdi-account-plus</v-icon>
    </div>

    <p v-if="userStore.loading" class="text-center">Caricamento...</p>

    <div v-if="userStore.utenti.length" class="mx-15">
      <RicercaUtente />

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Cognome</th>
            <th>Email</th>
            <th>Telefono</th>
            <th>Verificato</th>
            <th>Azioni</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="utente in userStore.filteredUtenti" :key="utente.id">
            <td>{{ utente.id }}</td>

            <td>
              <input
                v-if="userStore.editingId === utente.id"
                v-model="utente.nome"
                class="edit-input"
              />
              <span v-else class="view-span">{{ utente.nome }}</span>
            </td>

            <td>
              <input
                v-if="userStore.editingId === utente.id"
                v-model="utente.cognome"
                class="edit-input"
              />
              <span v-else class="view-span">{{ utente.cognome }}</span>
            </td>

            <td>
              <input
                v-if="userStore.editingId === utente.id"
                v-model="utente.email"
                class="edit-input"
              />
              <span v-else class="view-span">{{ utente.email }}</span>
            </td>

            <td>
              <input
                v-if="userStore.editingId === utente.id"
                v-model="utente.telefono"
                class="edit-input"
              />
              <span v-else class="view-span">{{ utente.telefono }}</span>
            </td>

            <td>
              <v-icon
                class="ml-5"
                :icon="utente.verificato ? 'mdi-check-circle' : 'mdi-close-circle'"
                :color="utente.verificato ? 'green' : 'red'"
              />
            </td>

            <td class="actions-cell">
              <v-icon
                v-if="userStore.editingId !== utente.id"
                @click="userStore.editingId = utente.id"
                color="blue"
                class="mr-2"
              >
                mdi-pencil-outline
              </v-icon>

              <v-icon v-else @click="handleSave(utente)" color="success" class="mr-2">
                mdi-content-save-outline
              </v-icon>

              <v-icon @click="userStore.viewReceipts(utente)" color="green">
                mdi-receipt-text-outline
              </v-icon>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.utenti-container {
  position: relative;
}

.add-user-container {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 20;
}

.add-user-btn {
  font-size: 2rem;
  cursor: pointer;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  table-layout: fixed;
}

th,
td {
  padding: 12px;
  border-bottom: 1px solid #ddd;
  text-align: left;
  overflow: hidden;
}

.edit-input {
  width: 100%;
  padding: 4px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

.view-span {
  display: inline-block;
  width: 100%;
  padding: 4px;
  border: 1px solid transparent;
  box-sizing: border-box;
}

.actions-cell {
  white-space: nowrap;
}
</style>
