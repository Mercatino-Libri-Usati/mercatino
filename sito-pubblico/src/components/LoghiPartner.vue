<template>
  <div class="partners-container">
    <h2 class="section-title">I Nostri Partner</h2>
    <div class="logo-grid">
      <div v-for="(p, i) in partners" :key="i" class="item">
        <a :href="p.link" target="_blank" rel="noopener" class="logo-link">
          <img :src="getUrl(p.file)" :alt="p.name" />
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
const partners = [
  { name: 'IIS Galilei', file: 'galilei.png', link: 'https://galileicrema.edu.it' },
  { name: 'Comune Crema', file: 'comune.png', link: 'https://comunecrema.cr.it' },
  { name: 'IIS Sraffa', file: 'sraffa.png', link: 'https://sraffacrema.edu.it' },
  { name: 'IIS Pacioli', file: 'pacioli.png', link: 'https://www.pacioli.edu.it' },
  {
    name: 'Consultorio Insieme',
    file: 'consultorio.png',
    link: 'https://www.diocesidicrema.it/consultorio-diocesano',
  },
  {
    name: 'Reti di Quarteri',
    file: 'rquart.jpg',
    link: 'https://percorsiconibambini.it/requcrema/',
  },
]
const getUrl = (name) => new URL(`../assets/${name}`, import.meta.url).href
</script>

<style scoped>
.partners-container {
  padding: 40px 0 20px;
  border-top: 2px solid #00d4ff;
  width: 100%;
  background: #ffffff;
}

.section-title {
  color: #00d4ff;
  text-align: center;
  font-family: sans-serif;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 2px;
  margin-bottom: 40px;
  opacity: 0.8;
}

.logo-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  gap: 40px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.item {
  display: flex;
  justify-content: center;
  align-items: center;
}

.logo-link {
  display: flex;
  justify-content: center;
  align-items: center;
  transition: transform 0.2s;
}

.logo-link:hover {
  transform: scale(1.1);
}

img {
  max-height: 160px;
  max-width: 160px;
  object-fit: contain;
}
</style>
