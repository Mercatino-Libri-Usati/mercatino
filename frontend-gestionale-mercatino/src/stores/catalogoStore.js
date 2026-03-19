import { defineStore } from 'pinia'
import { apiClient } from '@/apiConfig'
import { toast } from '@/toast'

// --- Logica dei Colori ---
const colors = [
  '#1976d2',
  '#388e3c',
  '#fbc02d',
  '#0288d1',
  '#f57c00',
  '#7b1fa2',
  '#009688',
  '#c2185b',
  '#43a047',
  '#ffa000',
  '#8d6e63',
  '#5c6bc0',
  '#00897b',
  '#f06292',
  '#ba68c8',
  '#ffd54f',
  '#4fc3f7',
  '#aed581',
  '#ff8a65',
  '#90caf9',
  '#a5d6a7',
  '#ffe082',
  '#ffab91',
  '#ce93d8',
  '#80cbc4',
  '#f48fb1',
  '#789262',
  '#b39ddb',
  '#ffb300',
  '#e57373',
  '#64b5f6',
  '#81c784',
  '#ffd740',
  '#ff7043',
  '#ab47bc',
  '#26a69a',
  '#d4e157',
  '#ffb74d',
]

function getChipColor(scuola, indirizzo, classe) {
  const key = scuola + indirizzo + (classe?.slice(0, 1) || '')
  let hash = 0
  for (let i = 0; i < key.length; i++) {
    hash = key.charCodeAt(i) + ((hash << 5) - hash)
  }
  return colors[Math.abs(hash % colors.length)]
}

export const useCatalogoStore = defineStore('catalogoStore', {
  state: () => ({
    catalogo: [],
    libriFiltrati: [],
    loading: false,
    error: null,
  }),

  getters: {
    getLibriDaMostrare: (state) => state.libriFiltrati,
    getChipColorFn: () => getChipColor,
  },

  actions: {
    async getCatalogo(force = false) {
      if (this.catalogo.length > 0 && !force) {
        if (!this.libriFiltrati.length) this.libriFiltrati = [...this.catalogo]
        return this.catalogo
      }

      this.loading = true
      this.error = null

      try {
        const { data } = await apiClient.get('/api/adozioni')
        this.catalogo = Array.isArray(data) ? data : []
        this.libriFiltrati = [...this.catalogo]
        return this.catalogo
      } catch (err) {
        this.error = err.response?.data || err.message || 'Errore API'
        return null
      } finally {
        this.loading = false
      }
    },

    /**
     * Filtra per Titolo, ISBN o Classe in modo compatto
     */
    filtraLibri(testo) {
      const query = testo?.toLowerCase().trim()

      if (!query) {
        this.libriFiltrati = [...this.catalogo]
        return
      }

      this.libriFiltrati = this.catalogo.filter((l) => {
        return (
          l.titolo?.toLowerCase().includes(query) ||
          l.isbn?.includes(query) ||
          Object.values(l.classi || {}).some((c) => c.classe?.toLowerCase().includes(query))
        )
      })
    },

    resetFiltro() {
      this.libriFiltrati = [...this.catalogo]
    },

    async updatePrezzo(id_catalogo, prezzo) {
      const prezzoNum = parseFloat(prezzo)
      if (isNaN(prezzoNum)) throw new Error('Prezzo non valido')

      try {
        await apiClient.patch(`/api/catalogo/${id_catalogo}/prezzo`, { prezzo: prezzoNum })

        // Aggiornamento reattivo di entrambi gli array
        const update = (lista) => {
          const libro = lista.find((l) => l.id_catalogo === id_catalogo)
          if (libro) libro.prezzo = prezzoNum
        }

        update(this.catalogo)
        update(this.libriFiltrati)

        return true
      } catch {
        toast.error('Errore aggiornamento prezzo')
        return false
      }
    },
  },
})
