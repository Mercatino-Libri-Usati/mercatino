<template>
  <nav class="navbar">
    <RouterLink to="/" class="brand" aria-label="Vai alla dashboard">
      <img src="../assets/logo_scritta.png" alt="Logo" class="logo" />
    </RouterLink>
    <!-- i router link fatti a icona-->
    <div class="icons" role="navigation" aria-label="Main navigation">
      <div class="icon-wrapper">
        <RouterLink to="/ritiro">
          <Icon icon="picon:receive" width="40" />
        </RouterLink>
        <span class="icon-text">Ritiro</span>
      </div>
      <div class="icon-wrapper">
        <RouterLink to="/vendita">
          <Icon icon="hugeicons:sale-tag-02" width="40" />
        </RouterLink>
        <span class="icon-text">Vendita</span>
      </div>
      <div class="icon-wrapper">
        <RouterLink to="/restituzione">
          <Icon icon="mdi:cash-refund" width="40" />
        </RouterLink>
        <span class="icon-text">Restituzione</span>
      </div>
    </div>
    <!-- quelli fatti a testo-->
    <div class="links">
      <RouterLink to="/magazzino">Magazzino</RouterLink>
      <RouterLink to="/prenotati">Prenotati</RouterLink>
      <RouterLink to="/utenti">Utenti</RouterLink>
      <RouterLink to="/cassa">Cassa</RouterLink>
      <RouterLink to="/ricevute">Ricevute</RouterLink>
      <RouterLink to="/catalogo">Catalogo</RouterLink>
      <RouterLink to="/report">Report</RouterLink>
    </div>

    <div class="utente">
      <v-text-field
        :model-value="nome_cognome"
        readonly
        density="compact"
        hide-details
        class="utente-text"
      />
      <v-icon @click="logout" style="cursor: pointer" title="Logout">mdi-logout</v-icon>
    </div>
  </nav>
</template>
<script setup>
defineOptions({ name: 'AppNavbar' })
import { Icon } from '@iconify/vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
import { computed } from 'vue'

const authStore = useAuthStore()
const router = useRouter()

const nome_cognome = computed(() => authStore.user?.nome_cognome || 'Utente')

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>
<style scoped>
.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: white;
  color: #2c3e50;
  padding: 10px 20px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.logo {
  height: 4rem;
  width: auto;
}

.icons,
.links {
  display: flex;
  align-items: center;
  gap: 20px;
}

.icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
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
  font-size: 0.9rem;
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

.icons a,
.links a {
  color: #2c3e50;
  text-decoration: none;
  font-weight: 500;
  font-size: 1.2rem;
}

.icons a:hover,
.links a:hover,
.icons a.router-link-exact-active,
.links a.router-link-exact-active,
.utente .iconify:hover {
  color: skyblue;
}

.utente {
  display: flex;
  align-items: center;
  gap: 15px;
  width: 10rem;
}
.utente-text {
  justify-content: center;
}
</style>
