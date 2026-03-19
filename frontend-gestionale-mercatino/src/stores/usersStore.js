import { defineStore } from 'pinia'
import { apiClient } from '@/apiConfig'

export const useUserStore = defineStore('userStore', {
  state: () => ({
    utenti: [],
    loading: false,
    error: null,
    searchQuery: '', // Per il componente RicercaUtente
    editingId: null, // Per gestire quale riga è in modifica
  }),

  getters: {
    // Filtro indipendente su tutti i campi richiesti
    filteredUtenti: (state) => {
      const query = state.searchQuery.toLowerCase().trim()
      if (!query) return state.utenti
      return state.utenti.filter(
        (u) =>
          u.nome?.toLowerCase().includes(query) ||
          u.cognome?.toLowerCase().includes(query) ||
          u.email?.toLowerCase().includes(query) ||
          u.telefono?.toLowerCase().includes(query),
      )
    },
  },

  actions: {
    async fetchUtenti() {
      this.loading = true
      try {
        const response = await apiClient.get('/api/elenco-utenti')
        this.utenti = response.data
      } catch {
        this.error = 'Errore nel caricamento utenti'
      } finally {
        this.loading = false
      }
    },

    async updateUser(utente) {
      try {
        // Chiamata per modificare  specifica per l'ID dell'utente
        await apiClient.patch(`/api/utente/${utente.id}`, {
          nome: utente.nome,
          cognome: utente.cognome,
          email: utente.email,
          telefono: utente.telefono,
        })
        this.editingId = null
        return true
      } catch (err) {
        console.error(err)
        alert('Errore durante il salvataggio')
        return false
      }
    },

    addUser() {
      alert('Funzione per aggiungere utente da implementare')
    },

    viewReceipts(utente) {
      alert(`Visualizzazione ricevute per: ${utente.nome}`)
    },
  },
})
