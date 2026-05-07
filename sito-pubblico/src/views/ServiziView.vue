<template>
  <div class="services">
    <NavbarHome />

    <main class="wrap">
      <header class="hero">
        <h1>Servizi</h1>
        <p class="sub">Date e orari chiari per ritiro, prenotazione, vendita e restituzione.</p>
      </header>

      <section class="grid">
        <article class="card">
          <header class="card-head">
            <span class="n">01</span>
            <div>
              <h2>Ritiro</h2>
              <p>Contovendita</p>
            </div>
          </header>

          <div class="schedule-list">
            <div class="schedule-row">
              <span class="schedule-date">28 Giu, 4–5–11–12–18 Lug</span>
              <span class="schedule-time">08:30 — 12:30</span>
            </div>
            <div class="schedule-row">
              <span class="schedule-date">4–11–18 Lug</span>
              <span class="schedule-time">15:00 — 18:00</span>
            </div>
          </div>

          <p class="desc">
            Porta i tuoi libri all' Aula Ristoro del Galilei nei giorni stabiliti.
            <br />
            Lascia a noi il compito di cercare di venderli. <br />Se verranno venduti, ricaverai il
            40%
            <br />
            Attraverso la tua <a href="/area-utente" target="_blank">area utente</a>, puoi vedere
            quali di questi libri che hai lasciato in conto vendita sono stati venduti
            effettivamente.
          </p>
        </article>

        <article class="card accent">
          <header class="card-head">
            <span class="n">02</span>
            <div>
              <h2>Prenotazione</h2>
              <p>Online</p>
            </div>
          </header>

          <div class="schedule-list">
            <div class="schedule-row">
              <span class="schedule-date">Dal 1 Luglio</span>
              <span class="schedule-time pill">Registrazione form</span>
            </div>
            <div class="schedule-row">
              <span class="schedule-date">Dal 21 Luglio</span>
              <span class="schedule-time pill">Apertura piattaforma</span>
            </div>
            <div class="schedule-row">
              <span class="schedule-date">Ritiro entro 25 Agosto</span>
              <span class="schedule-time">08:30 — 12:30</span>
            </div>
          </div>

          <p class="desc">
            Prenota attraverso <a href="/area-utente" target="_blank">la tua area utente</a> i libri
            che ti servono per l'anno scolastico che verrà.
          </p>
        </article>

        <article class="card">
          <header class="card-head">
            <span class="n">03</span>
            <div>
              <h2>Vendita</h2>
              <p>Acquisto</p>
            </div>
          </header>

          <div class="schedule-list">
            <div class="schedule-row">
              <span class="schedule-date">25–26–27–28–30 Ago, 4 Set</span>
              <span class="schedule-time">08:30 — 12:30</span>
            </div>
            <div class="schedule-row">
              <span class="schedule-date">29 Ago, 5 Set</span>
              <span class="schedule-time">15:00 — 18:00</span>
            </div>
          </div>

          <p class="desc">
            Vieni a comprare i libri che hai prenotato. Oppure comprali direttamente senza
            prenotazione, ma affrettati: vanno a ruba!
            <br />Li pagherai il 50% del prezzo di copertina.
          </p>
        </article>

        <article class="card">
          <header class="card-head">
            <span class="n">04</span>
            <div>
              <h2>Restituzione</h2>
              <p>Ricavato</p>
            </div>
          </header>

          <div class="schedule-list">
            <div class="schedule-row">
              <span class="schedule-date">6, 8, 9 Settembre</span>
              <span class="schedule-time">08:30 — 12:30</span>
            </div>
          </div>

          <p class="desc">
            Se hai lasciato dei libri in conto vendita, puoi venire a ritirare il ricavato della
            vendita (40%) e/o i libri invenduti.<br />
            Se vuoi sapere già prima di passare quanto hai guadnato, quanti libri hai venduto e
            quali sono quelli invenduti, puoi controllare tutto accedendo alla tua
            <a href="/area-utente" target="_blank">area personale</a>.
          </p>
        </article>
      </section>

      <section class="contact">
        <div class="box">
          <p>Hai dubbi? CONTATTACI</p>
          <a href="mailto:info@libriusaticrema.it">info@libriusaticrema.it</a>
        </div>
      </section>
    </main>

    <LoghiPartner />
    <FooterComponent />
  </div>
</template>

<script setup>
import FooterComponent from '@/components/FooterComponent.vue'
import NavbarHome from '../components/NavbarHome.vue'
import LoghiPartner from '@/components/LoghiPartner.vue'
</script>

<style scoped>
.services {
  background: #ffffff;
  min-height: 100vh;
  font-family: sans-serif;
}

.wrap {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px 90px;
}

.hero {
  margin: 10px 0 26px;
}

.hero h1 {
  font-size: 2.7rem;
  margin: 12px 0 6px;
  font-weight: 900;
}

.sub {
  margin: 0;
  color: #666;
  font-size: 1.05rem;
  line-height: 1.55;
  max-width: 70ch;
}

.grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 18px;
}

.card {
  grid-column: span 6;
  background: #fdfdfd;
  border: 1px solid #f0f0f0;
  border-radius: 20px;
  padding: 22px;
}

.card.accent {
  background: #00d4ff;
  border-color: #00d4ff;
  color: #fff;
}

.card a {
  color: rgba(30, 2, 167, 0.808);
}

.card a:hover {
  color: rgb(0, 0, 0);
}

.card-head {
  display: flex;
  gap: 14px;
  align-items: flex-start;
  margin-bottom: 14px;
}

.n {
  width: 42px;
  height: 42px;
  display: grid;
  place-items: center;
  border-radius: 12px;
  font-weight: 900;
  background: #00d4ff;
  color: #fff;
  flex-shrink: 0;
}

.card.accent .n {
  background: #fff;
  color: #000;
}

.card h2 {
  margin: 0;
  font-size: 1.6rem;
  font-weight: 900;
}

.card-head p {
  margin: 2px 0 0;
  color: #666;
  font-weight: 700;
}

.card.accent .card-head p {
  color: rgba(255, 255, 255, 0.92);
}

.schedule-list {
  margin: 10px 0 0;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;
  display: grid;
  gap: 10px;
}

.card.accent .schedule-list {
  border-top-color: rgba(255, 255, 255, 0.35);
}

.schedule-row {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
}

.schedule-date {
  font-weight: 900;
  line-height: 1.35;
}

.schedule-time {
  font-weight: 900;
  white-space: nowrap;
  background: rgba(0, 0, 0, 0.06);
  padding: 6px 10px;
  border-radius: 999px;
}

.card.accent .schedule-time {
  background: rgba(255, 255, 255, 0.25);
  color: #fff;
}

.pill {
  background: #000 !important;
  color: #fff !important;
}

.desc {
  margin: 14px 0 0;
  line-height: 1.6;
  color: #222;
  opacity: 0.85;
}

.card.accent .desc {
  color: #fff;
  opacity: 0.95;
}

.contact {
  margin-top: 18px;
}

.box {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  align-items: center;
  background: #fdfdfd;
  border: 1px solid #f0f0f0;
  border-radius: 20px;
  padding: 18px 20px;
}

.box p {
  margin: 0;
  font-weight: 900;
}

.box a {
  color: #00d4ff;
  text-decoration: none;
  border-bottom: 3px solid #00d4ff;
  font-weight: 900;
}

@media (max-width: 900px) {
  .card {
    grid-column: span 12;
  }

  .schedule-row {
    flex-direction: column;
  }

  .schedule-time {
    align-self: flex-start;
  }
}
</style>
