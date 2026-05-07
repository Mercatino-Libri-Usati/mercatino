<script setup>
import NavbarHome from '../components/NavbarHome.vue'
import FooterComponent from '@/components/FooterComponent.vue'
import LoghiPartner from '@/components/LoghiPartner.vue'
import ScrollImg from '@/components/ScrollImg.vue'
</script>

<template>
  <NavbarHome />
  <article style="background: #ffffff">
    <h2>Il Mercatino dei Libri Usati del Galilei</h2>
    <p>
      Il Mercatino dei Libri Usati del Galilei nasce nell'estate 2014, quando un gruppo di ragazzi
      delle classi 2e, del Liceo e dell'ITIS, si unirono per trovare una soluzione alla spesa dei
      libri di testo, che ogni anno ricade come un macigno sulle famiglie. Dopo un piccolo periodo
      di "briefing", dall'idea iniziale si passò ai fatti quando l'allora vicepreside, Enrico
      Fasoli, autorizzò l'iniziativa. Con una dozzina di ragazzi alla prima esperienza e con
      strumenti quasi rudimentali, iniziò nell'Aula Ristoro, il primo anno di servizio alla comunità
      scolastica del Galilei.
    </p>
    <p>
      Tra ritiro e vendita dei libri di testo sono state coinvolte 250 famiglie per un totale di 450
      libri venduti e generando un potenziale risparmio per le famiglie di 9.000€. Dopo il grande
      successo della prima edizione si decise di continuare l'anno successivo, anno ricco di
      miglioramenti nel campo organizzativo e gestionale, il quale ha portato a coinvolgere più di
      400 famiglie per un totale di 850 libri venduti e un risparmio stimato per le famiglie di
      18.000€. Un grande successo che testimonia come ormai il Mercatino di Libri Usati sia entrato
      a far parte della "routine" del Galilei.
    </p>
    <p>
      Il supporto della nuova dirigenza, insieme alla dedizione e al lavoro del gruppo di ragazzi
      anche durante l'anno scolastico, ha consentito miglioramenti all'aspetto gestionale ed
      organizzativo, così da dover impiegare un tempo ridotto per ogni singolo fruitore.
    </p>
  </article>

  <div class="stats">
    <div class="stat-card">
      <span class="num">400+</span>
      <span class="desc">famiglie coinvolte</span>
    </div>
    <div class="stat-card">
      <span class="num">850</span>
      <span class="desc">libri venduti</span>
    </div>
    <div class="stat-card">
      <span class="num">18.000€</span>
      <span class="desc">risparmio stimato</span>
    </div>
  </div>

  <article>
    <h2>Il nuovo Mercatino</h2>
    <p>
      Dal 2018 il Mercatino del Galilei si trasforma nel Mercatino dei libri usati di Crema grazie
      alla collaborazione tra gli studenti dell'Istituto Galilei e il Consultorio diocesano "Insieme
      per la famiglia", con il patrocinio della Consulta giovani e dell'Assessorato alle Politiche
      Giovanili del Comune di Crema. L'opportunità del mercatino viene estesa agli studenti di tutti
      gli istituti scolastici cittadini. Grazie al fondamentale contributo dello studente Pietro
      Donelli viene messo a punto il gestionale on-line con servizio di consultazione, prenotazione
      e gestione.
    </p>
    <p>
      Dopo il primo anno sperimentale, viene confermato nel 2019 il servizio per le scuole di Crema
      che hanno aderito ed hanno messo a disposizione spazi e studenti per la gestione. Nonostante
      la pandemia, nel 2020 il servizio viene faticosamente tenuto attivo, ma il successivo
      perdurare dell'emergenza nel 2021 e 2022 non ne ha permesso l'organizzazione.
    </p>

    <h2>Il Mercatino Rinasce!</h2>
    <p>
      Nel 2023, il mercatino rinasce grazie alla volontà dei rappresentanti degli studenti del
      Galilei, sempre con la gestione a cura del Consultorio diocesano "Insieme per la famiglia". Ad
      oggi l'iniziativa è sostenuta dal progetto <strong>ReQU – Reti dei quartieri</strong>,
      finanziato dalla Fondazione Percorsi con i bambini ed ha il patrocinio dell'Assessorato alle
      Politiche Giovanili e l'assessorato alla Formazione del Comune di Crema.
    </p>
    <p class="cta-text">Non potete mancare: <strong>vi aspettiamo!!</strong></p>
    <h2>Parlano di noi</h2>
    <p>
      <a
        href="https://sviluppo.crema-news.it/cronaca/crema-libri-usati-a-met-prezzo"
        target="_blank"
        rel="noopener noreferrer"
      >
        https://sviluppo.crema-news.it/cronaca/crema-libri-usati-a-met-prezzo
      </a>
    </p>
    <p>
      <a
        href="https://www.cremaonline.it/cultura/12-07-2025_Galilei:+mercatino+dei+libri+scolastici+usati+/"
        target="_blank"
        rel="noopener noreferrer"
      >
        https://www.cremaonline.it/cultura/12-07-2025_Galilei:+mercatino+dei+libri+scolastici+usati+/
      </a>
    </p>
    <h2>La Nostra Galleria</h2>
  </article>
  <ScrollImg />
  <LoghiPartner />
  <FooterComponent />
</template>
<style scoped>
article {
  max-width: 900px;
  margin: 3rem auto;
  padding: 0 1.5rem;
  line-height: 1.7;
  font-family:
    system-ui,
    -apple-system,
    sans-serif;
}

/* titoli */
article h2 {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: #00d4ff;
  position: relative;
}

article h2::after {
  content: '';
  display: block;
  width: 60px;
  height: 4px;
  background: linear-gradient(90deg, #00d4ff, rgb(251, 229, 229));
  margin-top: 0.5rem;
  border-radius: 2px;
}

/* paragrafi */
article p {
  margin-bottom: 1.2rem;
  font-size: 1.05rem;
}

/* grassetto */
strong {
  color: #00d4ff;
}

/* statistiche */
.stats {
  display: flex;
  justify-content: center;
  gap: 2rem;
  margin: 4rem auto;
  flex-wrap: wrap;
  max-width: 1000px;
  padding: 0 1rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem 2rem;
  text-align: center;
  color: #00d4ff;
  border: 1px solid black;
  transition: all 0.3s ease;
  min-width: 180px;
}

.stat-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
}

.num {
  display: block;
  font-size: 2rem;
  font-weight: bold;
  color: #00d4ff;
}

.desc {
  font-size: 0.95rem;
  color: #666;
}

.cta-text {
  margin-top: 1.5rem;
  font-size: 1.5rem;
  text-align: center;
}

/* responsive */
@media (max-width: 768px) {
  article h2 {
    font-size: 1.6rem;
  }

  .stats {
    gap: 1rem;
  }

  .stat-card {
    padding: 1.2rem;
  }
}
</style>
