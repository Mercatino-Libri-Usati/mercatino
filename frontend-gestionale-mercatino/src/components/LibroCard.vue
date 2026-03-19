<script setup>
import { defineAsyncComponent } from 'vue'
const LibroImmagine = defineAsyncComponent(() => import('@/components/LibroImmagine.vue'))

const props = defineProps({
  libro: {
    type: Object,
    required: true,
  },
  getChipColor: {
    type: Function,
    required: true,
  },
})

const emit = defineEmits(['modifica-prezzo'])

// Ordina le classi in modo numerico
const classiOrdinate = () => {
  const classi = props.libro.classi
  if (!classi) return []

  return Object.values(classi).sort((a, b) => {
    const valA = a?.classe || ''
    const valB = b?.classe || ''
    return String(valA).localeCompare(String(valB), undefined, { numeric: true })
  })
}

const onModificaPrezzo = () => {
  emit('modifica-prezzo', props.libro)
}
</script>

<template>
  <article class="card">
    <!-- Immagine -->
    <div class="card-img">
      <libro-immagine :isbn="libro.isbn" />
    </div>

    <!-- Contenuto -->
    <div class="card-body">
      <h2 class="titolo">{{ libro.titolo }}</h2>
      <p v-if="libro.sottotitolo" class="sottotitolo">{{ libro.sottotitolo }}</p>

      <div class="info">
        <p><strong>ISBN:</strong> {{ libro.isbn }}</p>
        <p><strong>Autore:</strong> {{ libro.autore }}</p>
        <div class="prezzo">
          <strong>Prezzo:</strong> {{ libro.prezzo }} €
          <span class="icon-wrapper">
            <v-icon
              icon="mdi-pencil"
              size="small"
              class="edit-btn"
              @click="onModificaPrezzo"
            ></v-icon>
            <span class="icon-text">Modifica prezzo</span>
          </span>
        </div>
      </div>

      <v-chip
        :color="libro.numero_disponibili === 0 ? 'red' : 'green'"
        style="
          position: absolute;
          top: 16px;
          right: 16px;
          height: auto;
          padding: 6px 10px;
          text-align: center;
          border-radius: 10px;
        "
      >
        <div>
          <span v-if="libro.numero_disponibili === 0">Non disponibile</span>
          <span v-else>Disponibile</span>
          <br />
          <span style="font-weight: bold; font-size: 1.1em">
            {{ libro.numero_disponibili }} / {{ libro.numero_libri }}
          </span>
        </div>
      </v-chip>

      <!-- Classi chips -->
      <div v-if="classiOrdinate().length" class="chips">
        <span
          v-for="(c, i) in classiOrdinate()"
          :key="i"
          class="chip"
          :title="`${c.scuola} - ${c.indirizzo}`"
          :style="{ backgroundColor: getChipColor(c.scuola, c.indirizzo, c.classe) }"
        >
          {{ c.classe }}
        </span>
      </div>
    </div>
  </article>
</template>

<style scoped>
.card {
  display: flex;
  gap: 16px;
  padding: 18px;
  background: #fff;
  border-radius: 10px;
  border: 1px solid #e0e0e0;
  box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
  position: relative;
}

.card-img {
  flex-shrink: 0;
  width: 100px;
  height: 140px;
}

.card-body {
  flex: 1;
  min-width: 0;
}

.titolo {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 4px;
  line-height: 1.3;
}

.sottotitolo {
  color: #666;
  font-size: 0.85rem;
  margin: 0 0 8px;
}

.info p {
  margin: 3px 0;
  font-size: 0.8rem;
}

.prezzo {
  display: flex;
  align-items: center;
  gap: 6px;
  position: relative;
}

.edit-btn {
  cursor: pointer;
  opacity: 0.6;
  transition:
    opacity 0.15s,
    transform 0.15s;
}

.edit-btn:hover {
  opacity: 1;
  transform: scale(1.15);
  color: #1976d2;
}

.icon-wrapper {
  position: relative;
  display: inline-block;
}

.icon-text {
  position: absolute;
  bottom: -30px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #2c3e50;
  color: white;
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 0.75rem;
  white-space: nowrap;
  z-index: 10;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  opacity: 0;
  visibility: hidden;
  transition:
    opacity 0.2s ease,
    visibility 0.2s ease;
}

.icon-wrapper:hover .icon-text {
  opacity: 1;
  visibility: visible;
}

.chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin: 8px 0;
}

.chip {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #222;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}
</style>
