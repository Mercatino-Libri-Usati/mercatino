import { defineStore } from 'pinia'
import { apiClient } from '@/apiConfig'

export const useVenditaStore = defineStore('vendita', {
  state: () => ({
    libriInVendita: [],
  }),
  getters: {
    totalePrezzo: (state) => {
      const total = state.libriInVendita.reduce((acc, libro) => acc + libro.prezzo, 0)
      return Math.round(total * 100) / 100
    },
  },
  actions: {
    async aggiungiLibro(numeroAnnuo) {
      const norm = (v) => String(v).replace(/^0+/, '')
      if (this.libriInVendita.some((l) => norm(l.numerolibro) === norm(numeroAnnuo))) {
        //se un libro lo ha già inserito , non può rifarlo
        throw 'Libro già inserito'
      }
      const { data } = await apiClient.get(`/api/util/isVendibile/${numeroAnnuo}?userid=1`)
      this.libriInVendita.push({
        id: data.id,
        isbn: data.isbn,
        titolo: data.titolo,
        prezzo: data.prezzo,
        numerolibro: data.numero_annuo,
      })
    },
    rimuoviLibro(index) {
      this.libriInVendita.splice(index, 1)
    },
    svuotaLibri() {
      this.libriInVendita = []
    },
  },
})
