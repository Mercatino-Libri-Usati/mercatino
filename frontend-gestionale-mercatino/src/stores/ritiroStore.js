import { defineStore } from 'pinia'
import { apiClient } from '@/apiConfig'

export const useRitiroStore = defineStore('ritiro', {
  state: () => ({
    libri: [],
  }),
  actions: {
    async aggiungiLibro(isbnPuro) {
      // La logica di fetch è qui, lo store gestisce solo i dati
      const { data } = await apiClient.get(`/api/util/isAccettato/${isbnPuro}`)
      this.libri.push({
        isbn: data.isbn,
        titolo: data.titolo || 'TITOLO NON DISPONIBILE',
        prezzo:
          typeof data.prezzo === 'string'
            ? parseFloat(data.prezzo.replace(',', '.'))
            : data.prezzo || 0,
        note: '',
      })
    },
    rimuoviLibro(index) {
      this.libri.splice(index, 1)
    },
    svuotaLibri() {
      this.libri = []
    },
  },
})
