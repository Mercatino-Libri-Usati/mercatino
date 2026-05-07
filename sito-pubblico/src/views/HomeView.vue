<template>
  <div class="home-container">
    <NavbarHome />
    <main class="content-wrapper">
      <section class="hero-header">
        <div class="logo-box">
          <img src="@/assets/logo_scritta.png" alt="Logo Mercatino" class="big-logo" />
        </div>
        <blockquote class="eco-quote">
          “I libri si rispettano usandoli, non lasciandoli stare”
          <cite>— Umberto Eco</cite>
        </blockquote>
      </section>

      <ServiziHome />

      <br />

      <MappaHome />
    </main>

    <LoghiPartner />
    <FooterComponent />
  </div>
</template>

<script setup>
import NavbarHome from '../components/NavbarHome.vue'
import LoghiPartner from '@/components/LoghiPartner.vue'
import FooterComponent from '@/components/FooterComponent.vue'
import MappaHome from '@/components/MappaHome.vue'
import ServiziHome from '@/components/ServiziHome.vue'
</script>

<style scoped>
.home-container {
  background: #ffffff;
  min-height: 100vh;
  font-family: sans-serif;
}

.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px 100px;
}

.hero-header {
  text-align: center;
  margin-bottom: 80px;
}

.big-logo {
  width: 100%;
  max-width: 15rem;
  height: auto;
  margin-bottom: 40px;
}

.eco-quote {
  font-size: 2rem;
  font-style: italic;
  color: #222;
  font-family: 'Georgia', serif;
}

.eco-quote cite {
  display: block;
  font-size: 0.9rem;
  font-style: normal;
  color: #00d4ff;
  font-weight: bold;
  margin-top: 10px;
  letter-spacing: 2px;
}
</style>
