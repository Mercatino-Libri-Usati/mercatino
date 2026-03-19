import { defineStore } from 'pinia'
import { apiClient } from '@/apiConfig'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.user,
  },

  actions: {
    async login(loginCredential, password) {
      this.loading = true
      this.error = null
      try {
        const response = await apiClient.post('/api/login', {
          login: loginCredential,
          password: password,
        })

        const { userid, privilegio, nome_cognome } = response.data

        this.user = { userid, privilegio, nome_cognome }

        return true
      } catch (err) {
        this.error = err.response?.data?.message || err.message || 'Login fallito'
        return false
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        await apiClient.post('/api/logout')
      } catch (e) {
        if (e.response && e.response.status !== 401) {
          console.error('Errore durante il logout:', e)
        }
      } finally {
        this.user = null
      }
    },

    async whoami() {
      if (this.user) return true
      this.loading = true
      this.error = null
      try {
        const response = await apiClient.get('/api/whoami')
        const { userid, privilegio, nome_cognome } = response.data
        this.user = { userid, privilegio, nome_cognome }
        return true
      } catch (err) {
        this.user = null
        // Se l'errore è 401, siamo sloggati, altrimenti è un errore
        if (err.response && err.response.status !== 401) {
          this.error = err.response?.data?.message || err.message || 'Errore nel caricamento utente'
        }
        return false
      } finally {
        this.loading = false
      }
    },
  },
})
