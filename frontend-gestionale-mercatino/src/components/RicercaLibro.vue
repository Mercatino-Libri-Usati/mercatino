<script setup>
import { ref, watch } from 'vue'
import { useCatalogoStore } from '@/stores/catalogoStore'

const catalogoStore = useCatalogoStore()

const searchValue = ref('')
// Variabile per gestire il debounce
let debounceTimeout = null

// Funzione che esegue effettivamente il filtro (chiama l'azione dello store)
function eseguiFiltro() {
  catalogoStore.filtraLibri(searchValue.value)
}

// Funzione Debounced: ritarda l'esecuzione del filtro
function debouncedFiltro() {
  clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => {
    eseguiFiltro()
  }, 300) // Filtra solo dopo che l'utente ha smesso di digitare per 300ms
}

// reset filtro
function resetFiltro() {
  searchValue.value = ''
  catalogoStore.resetFiltro()
  clearTimeout(debounceTimeout) // Rimuovi qualsiasi debounce pendente
}

// watch live input - ora chiama la funzione debounced
watch(searchValue, debouncedFiltro)
</script>

<template>
  <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 15px">
    <button
      type="button"
      @click="resetFiltro"
      title="Reset filtro"
      aria-label="Reset filtro"
      style="
        padding: 4px 8px;
        border-radius: 4px;
        border: none;
        background: #eee;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
      "
    >
      <v-icon>mdi-restart</v-icon>
    </button>

    <input
      v-model="searchValue"
      placeholder="Cerca per titolo, ISBN o classe..."
      style="flex: 1; padding: 4px 6px; border: 1px solid #ccc; border-radius: 4px"
    />
  </div>
</template>
