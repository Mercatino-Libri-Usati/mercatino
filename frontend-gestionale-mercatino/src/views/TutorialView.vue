<template>
  <main class="tutorial-page page-container">
    <header class="hero">
      <p class="eyebrow">Guida pratica</p>
      <h1>Come usare il gestionale del mercatino</h1>
      <p>
        Questa pagina è pensata per chi lavora al mercatino. Qui trovi i passaggi essenziali per
        usare le funzioni principali senza perdere tempo.
      </p>
    </header>

    <section class="card">
      <h2>Dashboard</h2>
      <p>
        Nella schermata iniziale trovi un riepilogo rapido: quanti libri sono stati ritirati,
        venduti e quanti sono ancora disponibili.
      </p>
    </section>

    <section class="card">
      <h2>Ritiro libri</h2>
      <p>Accedi cliccando sull’icona <strong>Ritiro</strong> nel menu.</p>
      <ol>
        <li>Seleziona l’utente.</li>
        <li>Clicca sul campo ISBN.</li>
        <li>Scansiona il codice del libro.</li>
        <li>Premi invio per aggiungerlo.</li>
        <li>Ripeti per tutti i libri (max 20).</li>
        <li>Se sbagli, devi riscansionare il libro.</li>
        <li>Clicca <strong>Registra nuovi libri</strong> quando hai finito.</li>
        <li>Stampa la ricevuta e fai firmare.</li>
      </ol>
    </section>

    <section class="card">
      <h2>Vendita</h2>
      <p>Apri dalla sezione <strong>Vendita</strong>.</p>
      <ol>
        <li>Seleziona l’utente.</li>
        <li>Inserisci il codice del libro (non l’ISBN).</li>
        <li>Aggiungi tutti i libri da vendere (max 20).</li>
        <li>Completa la vendita e stampa la ricevuta.</li>
      </ol>
    </section>

    <section class="card">
      <h2>Restituzione</h2>
      <p>Qui gestisci i libri non venduti e il rimborso.</p>
      <ol>
        <li>Seleziona l’utente.</li>
        <li>Controlla importo e libri.</li>
        <li>Stampa la ricevuta e fai firmare.</li>
      </ol>
    </section>

    <section class="card">
      <h2>Altre sezioni</h2>
      <ul>
        <li><strong>Magazzino:</strong> stato dei libri</li>
        <li><strong>Prenotati:</strong> libri riservati</li>
        <li><strong>Utenti:</strong> dati partecipanti</li>
        <li><strong>Cassa:</strong> situazione soldi</li>
        <li><strong>Ricevute:</strong> archivio operazioni</li>
        <li><strong>Catalogo:</strong> libri adottati</li>
        <li><strong>Report:</strong> riepiloghi automatici</li>
      </ul>
    </section>
  </main>
</template>

<style scoped>
.tutorial-page {
  font-family:
    system-ui,
    -apple-system,
    Segoe UI,
    Roboto,
    sans-serif;
  color: #1a1a1a;
  background-color: #fafafa;
}

/* HEADER */
.hero {
  margin-bottom: 28px;
  margin-top: 2rem;
}

.eyebrow {
  font-size: 12px;
  font-weight: 600;
  color: #888;
  text-transform: uppercase;
  margin-bottom: 6px;
  letter-spacing: 0.04em;
}

h1 {
  font-size: 26px;
  margin: 0;
  font-weight: 600;
}

h2 {
  font-size: 17px;
  margin-bottom: 10px;
  font-weight: 600;
}

p {
  font-size: 14.5px;
  line-height: 1.6;
  color: #444;
  margin: 6px 0 0;
}

/* CARD */
.card {
  background: #ffffff;
  border: 1px solid #e6e6e6;
  border-radius: 10px;
  padding: 16px 18px;
  margin-bottom: 16px;
  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease;
}

.card:hover {
  border-color: #d0d0d0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

/* LISTE */
ol,
ul {
  margin-top: 10px;
  padding-left: 18px;
}

li {
  margin-bottom: 6px;
  font-size: 14px;
}

/* STRONG */
strong {
  font-weight: 600;
  color: #111;
}

/* MOBILE */
@media (max-width: 600px) {
  .tutorial-page {
    padding: 16px 12px;
  }

  h1 {
    font-size: 22px;
  }
}
</style>
