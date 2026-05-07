<template>
  <nav class="navbar">
    <RouterLink to="/home" class="logo-link">
      <img src="@/assets/logo_scritta.png" alt="Logo" class="logo" />
    </RouterLink>

    <button class="burger" @click="open = !open" :class="{ active: open }" aria-label="Apri menu">
      <span /><span /><span />
    </button>

    <div class="links" :class="{ open }">
      <RouterLink to="/home" @click="open = false">Home Page</RouterLink>
      <RouterLink to="/adozioni" @click="open = false">Adozioni</RouterLink>
      <RouterLink to="/servizi" @click="open = false">Servizi</RouterLink>
      <RouterLink to="/chi-siamo" @click="open = false">Chi siamo</RouterLink>
      <RouterLink to="/area-utente" @click="open = false">Area utente</RouterLink>
      <RouterLink to="/registrazione" @click="open = false" class="cta">Registrati</RouterLink>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
const open = ref(false)
</script>

<style scoped>
.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: #ffffff;
  color: #2c3e50;
  padding: 10px 20px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 100;

  font-family: sans-serif;
  font-size: 16px;
  line-height: 1.2;
}

.logo-link {
  display: flex;
  align-items: center;
}

.logo {
  height: 4rem;
  width: auto;
  display: block;
  flex-shrink: 0;
}

.links {
  display: flex;
  align-items: center;
  gap: 20px;
}

.links :deep(a) {
  color: #2c3e50;
  text-decoration: none;
  font-weight: 500;
  font-size: 1rem;
  line-height: 1.2;
  transition: color 0.15s;
  white-space: nowrap;
}

.links :deep(a:hover),
.links :deep(a.router-link-active) {
  color: skyblue;
}

.cta {
  border: 1.5px solid #2c3e50;
  padding: 6px 16px;
  border-radius: 6px;
}

.cta:hover {
  border-color: skyblue;
}

.burger {
  display: none;
  flex-direction: column;
  gap: 5px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
}

.burger span {
  display: block;
  width: 22px;
  height: 2px;
  background: #2c3e50;
  transition: all 0.2s;
}

.burger.active span:nth-child(1) {
  transform: translateY(7px) rotate(45deg);
}

.burger.active span:nth-child(2) {
  opacity: 0;
}

.burger.active span:nth-child(3) {
  transform: translateY(-7px) rotate(-45deg);
}

@media (max-width: 768px) {
  .burger {
    display: flex;
  }

  .links {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    flex-direction: column;
    align-items: stretch;
    gap: 0;
    background: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .links.open {
    display: flex;
  }

  .links :deep(a) {
    padding: 14px 20px;
    border-top: 1px solid #f0f0f0;
    font-size: 1rem;
  }

  .cta {
    border: none;
    border-top: 1px solid #f0f0f0 !important;
    border-radius: 0;
  }
}
</style>
