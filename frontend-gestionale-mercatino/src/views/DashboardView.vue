<script setup>
import { ref, onMounted } from 'vue'
import { apiClient } from '@/apiConfig'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const stats = ref(null)

onMounted(async () => {
  try {
    const res = await apiClient.get('/api/stats')
    stats.value = res.data
  } catch (error) {
    console.error('Errore nel caricamento stats:', error)
  }
})
</script>

<template>
  <div class="page-container">
    <div v-if="stats" class="stats-card">
      <div class="header">
        <h2 class="welcome">Benvenuto, {{ authStore.user?.nome_cognome }}</h2>
      </div>

      <table class="stats-table">
        <tbody>
          <tr>
            <td>
              <v-icon icon="mdi-book-open-page-variant" color="blue" size="32" />
              Libri totali
            </td>
            <td>{{ stats.n_libri }}</td>
          </tr>

          <tr>
            <td>
              <v-icon icon="mdi-check-decagram-outline" color="green" size="32" />
              Libri venduti
            </td>
            <td>{{ stats.venduti }}</td>
          </tr>

          <tr>
            <td>
              <v-icon icon="mdi-account-multiple-outline" color="indigo" size="32" />
              Utenti
            </td>
            <td>{{ stats.n_utenti }}</td>
          </tr>

          <tr>
            <td>
              <v-icon icon="mdi-cash-minus" color="red" size="32" />
              Profitto
            </td>
            <td>{{ stats.profitto }} €</td>
          </tr>

          <tr>
            <td>
              <v-icon icon="mdi-cash-plus" color="green" size="32" />
              Ricavo
            </td>
            <td>{{ stats.ricavo }} €</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="loading">Caricamento statistiche...</div>
  </div>
</template>
<style scoped>
.page-container {
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Card centrale */
.stats-card {
  width: 900px;
  border-radius: 20px;
  padding: 40px;
}

/* Header */
.header {
  text-align: center;
  margin-bottom: 40px;
}

.header h2 {
  font-size: 48px;
  margin-bottom: 20px;
}

.welcome {
  color: #4338ca;
}

/* Tabella */
.stats-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 22px;
}

.stats-table tr {
  border-bottom: 1px solid #ddd; /* Separatore tra le righe */
}

.stats-table td {
  padding: 18px 10px;
}

.stats-table td:first-child {
  gap: 15px;
  font-weight: bold;
}

.stats-table td:last-child {
  text-align: right;
  font-weight: bold;
}

/* Loading */
.loading {
  font-size: 22px;
  font-weight: bold;
  color: #555;
}
</style>
