<template>
  <NavbarHome />
  <div class="page-404">
    <p class="bg-number">404</p>
    <span class="tag">Pagina non trovata</span>
    <h1>Questa pagina non esiste</h1>
    <p class="sub">Verifica l'URL oppure <RouterLink to="/">torna alla home</RouterLink>.</p>
  </div>
</template>

<style scoped>
.page-404 {
display: flex;
flex-direction: column;
align-items: center;
justify-content: center;
text-align: center;
padding: 2rem;
background: #ffffff;
}

.bg-number {
  font-size: clamp(7rem, 20vw, 12rem);
  font-weight: 700;
  opacity: 0.07;
  margin: 0;
  line-height: 1;
  user-select: none;
}

.tag {
  margin-top: -2rem;
  font-size: 11px;
  letter-spacing: 2px;
  color: #888;
  border: 0.5px solid #ccc;
  padding: 4px 14px;
  border-radius: 20px;
}

h1 {
  font-size: clamp(1.4rem, 4vw, 2rem);
  margin: 1.2rem 0 0.5rem;
}
</style>
<script setup>
import NavbarHome from '../components/NavbarHome.vue'
</script>
