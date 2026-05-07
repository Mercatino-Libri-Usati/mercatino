<template>
  <footer class="footer">
    <div class="accent"></div>

    <div class="content">
      <div class="grid">
        <div class="col">
          <h2 class="logo">Mercatino <span class="blue">Libri Usati</span></h2>
          <p class="text">
            Il punto di riferimento per i libri scolastici usati per le scuole di Crema
          </p>
        </div>

        <div class="col">
          <h3 class="title">Contatti</h3>
          <a href="mailto:info@libriusaticrema.it" class="link"> info@libriusaticrema.it </a>
        </div>

        <div class="col">
          <h3 class="title">Sede</h3>
          <p class="text">
            <strong>IIS G. Galilei</strong><br />
            Via Canossa, 21 - Crema
          </p>
        </div>
      </div>

      <div class="bottom">
        <p>
          © {{ new Date().getFullYear() }}
          <a href="https://consultorioinsieme.it/" target="_blank" rel="noopener noreferrer"
            >Consultorio familiare diocesano "Insieme"</a
          >
        </p>
        <p class="credits">Dev: <strong>AP, SFB</strong></p>
        <!-- AP & DP , NMT -->
      </div>
    </div>
  </footer>
</template>

<style>
/* Reset fondamentale per zero spazi bianchi */
html,
body {
  margin: 0;
  padding: 0;
  height: 100%;
}

#app {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

main {
  flex: 1;
}

/* Spinge il footer in basso */
</style>

<style scoped>
.footer {
  width: 100%;
  color: #000000;
  font-family: sans-serif;
  margin-top: auto;
  background: #ffffff;
}

.accent {
  height: 4px;
  background: #00d4ff;
  /* Azzurro brillante */
}

.content {
  max-width: 1100px;
  margin: 0 auto;
  padding: 40px 20px 20px;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 30px;
  padding-bottom: 30px;
}

.logo {
  font-size: 1.4rem;
  margin: 0 0 10px;
  color: #00d4ff;
}

.blue {
  color: #00d4ff;
}

.title {
  color: #00d4ff;
  font-size: 0.9rem;
  text-transform: uppercase;
  margin-bottom: 15px;
}

.text,
.link {
  font-size: 0.9rem;
  line-height: 1.5;
  text-decoration: none;
  color: #000000;
}

.link:hover {
  color: #00d4ff;
}

.bottom {
  border-top: 1px solid #222;
  padding-top: 20px;
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
}

/* Responsive rapido */
@media (max-width: 600px) {
  .grid {
    text-align: center;
  }

  .bottom {
    flex-direction: column;
    align-items: center;
    gap: 10px;
  }
}
</style>
