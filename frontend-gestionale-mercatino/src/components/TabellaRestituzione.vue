<script setup>
defineProps({
  libri: {
    type: Array,
    required: true,
  },
})
</script>

<template>
  <div v-if="libri.length > 0" class="tabella-wrapper">
    <div class="table-container">
      <!-- intestazioni -->
      <div class="table-header">
        <div class="header-cell" style="width: 15%">Stato</div>
        <div class="header-cell" style="width: 15%">ISBN</div>
        <div class="header-cell" style="width: 40%">Titolo</div>
        <div class="header-cell" style="width: 15%">Prezzo (€)</div>
        <div class="header-cell" style="width: 15%">Numero Libro</div>
      </div>

      <!-- righe -->
      <div
        v-for="(libro, index) in libri"
        :key="index"
        class="table-row"
        :class="{ 'row-invenduto': !libro.venduto, 'row-venduto': libro.venduto }"
      >
        <!-- stato -->
        <div style="width: 15%" class="table-cell">
          <v-icon :color="libro.venduto ? 'green' : 'red'" size="small" class="mr-2">
            {{ libro.venduto ? 'mdi-cash-multiple' : 'mdi-alert-circle' }}
          </v-icon>
          <span>{{ libro.venduto ? 'Venduto' : 'Invenduto' }}</span>
        </div>

        <!-- isbn -->
        <div style="width: 15%" class="table-cell">
          {{ libro.isbn }}
        </div>

        <!-- titolo -->
        <div style="width: 40%" class="table-cell">
          {{ libro.titolo }}
        </div>

        <!-- prezzo -->
        <div style="width: 15%" class="table-cell">€ {{ libro.prezzo }}</div>

        <!-- numero libro -->
        <div style="width: 15%" class="table-cell">
          {{ libro.numero_libro }}
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.tabella-wrapper {
  padding: 16px;
}

.table-container {
  display: flex;
  flex-direction: column;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e0e0e0;
}

/* Header */
.table-header {
  display: flex;
  background: linear-gradient(135deg, #3f51b5 0%, #5c6bc0 100%);
  padding: 16px;
  font-weight: 600;
  color: white;
  font-size: 0.85rem;
  text-transform: uppercase;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header-cell {
  display: flex;
  align-items: center;
  padding: 0 12px;
}

.header-cell:first-child {
  padding-left: 0;
}

/* Rows */
.table-row {
  display: flex;
  padding: 0;
  border-bottom: 1px solid #eeeeee;
}

.table-row:last-child {
  border-bottom: none;
}

.table-row.row-invenduto {
  background-color: #ffebee;
}

.table-row.row-venduto {
  background-color: #ffffff;
}

/* Cella */
.table-cell {
  display: flex;
  align-items: center;
  padding: 16px 12px;
  font-size: 0.95rem;
  color: #333;
}

.table-cell:first-child {
  padding-left: 0;
}

.mr-2 {
  margin-right: 6px;
}
</style>
