<template>
  <div>
    <section class="location-grid">
      <div class="info-text">
        <span class="badge">COSA FACCIAMO</span>
        <h1>Ritiriamo i <strong>TUOI</strong> libri usati</h1>
        <br />
        <h1>E li ridistribuiamo <strong>A METÀ PREZZO</strong></h1>
        <p class="sub-text">È possibile anche la prenotazione online</p>
        <div class="cta-area">
          <a href="/servizi" class="btn-map"> Scopri le date e come funziona </a>
        </div>
      </div>

      <div class="personal-area-box">
        <div class="steps-highlight">
          <a href="#come-funziona" class="step-link">1. RITIRO</a>
          <a href="#come-funziona" class="step-link">2. PRENOTAZIONE</a>
          <a href="#come-funziona" class="step-link">3. VENDITA</a>
          <a href="#come-funziona" class="step-link">4. RESTITUZIONE</a>
        </div>

        <h2>Area personale</h2>
        <p>
          Attraverso la tua area personale, puoi prenotare i libri per l'anno prossimo e tenere
          traccia dei tuoi libri in conto vendita.
        </p>

        <a href="/area-utente" class="btn-primary">Accedi alla tua area</a>
      </div>
    </section>

    <section id="come-funziona" class="servizi-section">
      <h2 class="section-title">Come funziona?</h2>
      <p>
        Trovi tutte le informazioni nel dettaglio anche in
        <a href="/servizi" target="_blank">questa pagina</a>
      </p>
      <div class="servizi-list">
        <div class="servizio-item">
          <h3>Ritiro</h3>
          <p class="sub-text">
            Porta i tuoi libri all' Aula Ristoro del Galilei nei giorni stabiliti.
            <br />
            Lascia a noi il compito di cercare di venderli.
          </p>
        </div>

        <div class="servizio-item">
          <h3>Prenotazione - da casa</h3>
          <p class="sub-text">
            Prenota attraverso <a href="/area-utente" target="_blank">la tua area utente</a> i libri
            che ti servono per l'anno scolastico che verrà.
          </p>
        </div>

        <div class="servizio-item">
          <h3>Vendita</h3>
          <p class="sub-text">
            Vieni a comprare i libri che hai prenotato. Oppure comprali direttamente senza
            prenotazione, ma affrettati: vanno a ruba!
          </p>
        </div>

        <div class="servizio-item">
          <h3>Restituzione</h3>
          <p class="sub-text">
            Se hai lasciato dei libri in conto vendita, puoi venire a ritirare il ricavato della
            vendita e/o i libri invenduti.
          </p>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
/* Stili per il box Area Personale */
.personal-area-box {
  background: #ffffff;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  border: 1px solid #f0f0f0;
}

/* Risalto per le 4 fasi */
.steps-highlight {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 25px;
}

.step-link {
  display: block;
  background: #000;
  color: #fff !important; /* Forza il colore bianco */
  padding: 10px;
  text-align: center;
  font-weight: 900;
  font-size: 0.9rem;
  text-decoration: none;
  border-radius: 4px;
  transition: transform 0.2s;
}

.step-link:hover {
  transform: scale(1.05);
  background: #00d4ff;
}
a:hover {
  color: #00d4ff;
}
.personal-area-box h2 {
  font-size: 1.8rem;
  margin-bottom: 10px;
}

.personal-area-box p {
  color: #555;
  margin-bottom: 25px;
  line-height: 1.5;
}

/* Pulsante per Area Personale */
.btn-primary {
  display: inline-block;
  background: #00d4ff;
  color: #fff !important;
  padding: 12px 20px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
}

/* --- Layout e Sezioni --- */
.location-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
  background: #fdfdfd;
  padding: 40px;
  border-radius: 20px;
  border: 1px solid #f0f0f0;
}

.servizi-section {
  margin-top: 100px;
  text-align: center;
  scroll-margin-top: 50px; /* Spazio extra quando si clicca il link */
}

.section-title {
  font-size: 2rem;
  font-weight: 800;
  margin: 20px 0 50px;
}

.servizi-list {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 35px;
}

.servizio-item {
  background: #ffffff;
  padding: 30px;
  border-radius: 16px;
  border: 1px solid #f0f0f0;
  text-align: left;
}

.servizio-item h3 {
  font-size: 1.4rem;
  margin-bottom: 12px;
  color: #00d4ff;
}

.badge {
  background: #00d4ff;
  color: #fff;
  padding: 5px 12px;
  font-size: 1.5rem;
  font-weight: bold;
  border-radius: 4px;
}

h1 {
  font-size: 2rem;
  margin: 15px 0;
  font-weight: 800;
}

strong {
  background: #000;
  color: #fff;
  padding: 2px 8px;
}

.btn-map {
  display: inline-block;
  margin-top: 25px;
  background: #00d4ff;
  color: #fff;
  text-decoration: none;
  padding: 12px 25px;
  border-radius: 6px;
  font-weight: bold;
}

a {
  color: black;
}

@media (max-width: 900px) {
  .location-grid {
    grid-template-columns: 1fr;
    text-align: center;
  }
  .servizi-list {
    grid-template-columns: 1fr;
  }
}
</style>
