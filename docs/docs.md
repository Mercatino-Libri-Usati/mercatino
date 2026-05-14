# Documentazione progetto - Sito mercatino

# 1. Testo

Libri Usati Crema (anche chiamato “mercatino" ) é un iniziativa del consultorio diocesano per combattere la crescente spesa dei libri di testo scolastci, che ogni anno ricade come un macigno sulle famiglie.

Il progetto nasce dall’esigenza di mantenere e migliorare il sito web e il gestionale del mercatino, che ha oltre 10 anni e costantemente necessita di correzioni.
Negli ultimi anni, il codice esistente è diventato difficile da gestire e correggere. Gli interventi
di manutenzione hanno progressivamente aumentato la complessità del sistema, fino al
punto in cui intervenire sull’attuale codice non è più realistico.
Per questi motivi, si è deciso di ripartire da zero, realizzando una nuova versione del sito e gestionale 
basata su tecnologie moderne, più scalabili e mantenibili.

# 2. Analisi del contesto

Prima iniziare il progetto abbiamo effettuato un’analisi, unendo le nostre opinioni,  non solo in qualità di sviluppatori, ma prima di  gestori-volontari  al mercatino , con quelle degli altri stakeholder . 

### Stakeholder

| Nome | Ruolo e coinvolgimento |
| --- | --- |
| **Prof. Davide Pagliarini** | Supervisore tecnico / guida del progetto. 
Alto potere e alto interesse. 
Aggiornato e consultato regolarmente. |
| **Consultorio / Veruska Stanga** | Beneficiario indiretto del progetto. 
Alto potere ma basso interesse. 
Verrà informata al completamento. |
| **Volontari del Mercatino** | Utilizzatori principali del sistema. 
Basso potere decisionale, alto interesse. 
Verranno consultati prima della realizzazione e formati all’uso del nuovo sito. |
| **Clienti del mercatino** | Utenti finali del servizio. Basso potere e basso interesse. Beneficeranno di prenotazioni più semplici e minori tempi di attesa. |
| **Project Manager** | Parolari Andrea |
| **Project Team** | Fusar Bassini Simone – Frontend Developer
Parolari Andrea – Backend Developer |

# 3. Ipotesi

| **Voce** | **Descrizione** |
| --- | --- |
| **Constraints (Vincoli)** | Infrastruttura server limitata e mediocre. Database non modificabile in alcune sue parti (retrocompatibilità)
Nessun budget per software o licenze aggiuntive. Deadline fissa a maggio 2025. |
| **Assumptions (Assunzioni)** | Accesso garantito al database e ai sorgenti precedenti.
Disponibilità del prof. Pagliarini per supporto tecnico. 
I volontari collaboreranno per il testing e forniranno feedback. 
Requisiti invariati rispetto a gli anni scorsi |
| **Dependencies (Dipendenze)** | Compatibilità con il database attuale. 
Utilizzo del repository GitHub condiviso. 
Dipendenza da librerie e strumenti  (Vue.js, compose, altre librerie “on demand”). |
| **Risks (Rischi)** | - Problemi di compatibilità con il vecchio database.
- Bug critici non rilevati durante i test.
- Difficolta di comprensione del codice vecchio.
- Possibili difficoltà di integrazione con il sito pubblico. |

# 4. Obiettivi

1. Re-implementare tutte le funzionalità già esistenti .
2. Aggiungere almeno la gestione delle ricevute di restituzione e la  contabilità di cassa.
3. Garantire stabilità della piattaforma dopo la fase di testing (assenza di bug noti o workaround).
4. Migliorare le performance complessive del sito (tempi di risposta e caricamento sotto il secondo).

# 5. Target

Il progetto  si rivolge a quelli che sono i nostri stakeholder : 

| Nome | Ruolo e coinvolgimento |
| --- | --- |
| **Prof. Davide Pagliarini** | Supervisore tecnico / guida del progetto. 
Alto potere e alto interesse. 
Aggiornato e consultato regolarmente. |
| **Consultorio / Veruska Stanga** | Beneficiario indiretto del progetto. 
Alto potere ma basso interesse. 
Verrà informata al completamento. |
| **Volontari del Mercatino** | Utilizzatori principali del sistema. 
Basso potere decisionale, alto interesse. 
Verranno consultati prima della realizzazione e formati all’uso del nuovo sito. |
| **Clienti del mercatino** | Utenti finali del servizio. Basso potere e basso interesse. Beneficeranno di prenotazioni più semplici e minori tempi di attesa. |

# UML

UML disponibile nella cartella ./docs/casi d'uso.png

## User stories

### Funzionalità del gestore

- Voglio registrare nuovi libri, che ricevo servendo i clienti.
- Voglio vendere un libro a un cliente, facendo una transazione.
- Voglio registrare una restituzione di libri e soldi agli utenti, aggiornando la disponibilità di libri.
- Voglio visualizzare le informazioni sul mercatino, così da sapere quali libri sono disponibili, prenotati o venduti.
- Voglio mantenere la contabilità di cassa e il controllo dei libri.

### Funzionalità del cliente

- Voglio registrarmi o fare il login, così da poter accedere al servizio.
- Voglio prenotare uno o più libri, così da assicurarmi una copia disponibile.
- Voglio vedere lo stato della mia prenotazione/vendita, così da sapere se il mio ordine è confermato, in attesa o completato.

### Funzionalità amministratore

- Voglio importare i dati delle adozioni, così da aggiornare le informazioni sui libri accettati ed il loro prezzo.
- Voglio creare e gestire gli account dei gestori, a partire da un account utente.

# 7/8. Strategia di soluzione e implementazione

### **Backend**

Il backend è stato realizzato in Laravel, un framework di PHP , questo perchè lo abbiamo ritenuto molto comodo e semplice per realizzare un backend indipendente che si interfacci con il frontend attraverso un API.

Il principale vantaggio di questa scelta è che eventuali bug o errori sul frontend, non vanno a intaccare il backend e viceversa.

Usiamo una libreria sviluppata dal team di Laravel (Sanctrum) per la gestione dell’autenticazione. Questa avviene tramite un cookie http-only contenente un token slavato anche nel database.

Nella cartella ./docs/api è disponibile la specifica openapi e un file html per visulizzarla.

### **Frontend**

Il frontend è stato realizzato in VUE.JS, un framework JS, ottimo per realizzare componenti dinamici e riutilizzabili.

Il frontend si interfaccia con il backend attraverso delle chiamate alla API.

### **Database**

Il Database è un database classico SQL.

Siamo partiti dal DB già esistente del mercatino , ma sono state effettuate delle ripulizie, in funzione alle analisi da noi effettuate.

Il diagramma del database è disponibile nella cartella ./docs/database.

# 9. Distribuzione

Attualmente il sito é costato su TopHost, con un piano di tipo managed che include un server web (Apache), PHP, un database MySql, un certificato TLS e un dominio (libriusaticrema.it).

Sono noti problemi di prestazioni (legati al cold boot) e  la mancanza  dell'accesso SSH (che semplificherebbe il deployment).

# 10. Valutazione

### Prestazioni

L’api ha, in un’ambiente di produzione ideale, tempi di risposta sotto i 100ms per la maggioranza delle rotte e sotto il secondo per tutte le richieste.

### Testing

Il backend é stato analizzato staticamente da Phpstan (livelli 0 a 9), superando tutti i test.

Il frontend é stato analizzato dal tool di analisi statica EsLint senza alcun errore segnalato.

Il sito é stato scansionato con SqlMap per la ricerca automatica di vulnerabilità senza che nessuna venisse trovata.

### Soddisfazione stakeholder

Il prof Pagliarini si è dichiarato molto soddisfatto del progetto parziale

# **11. SVILUPPI FUTURI**

Necessari:

- Aggiornamento area utente
- Migrazione vecchi dati

Ipotetici:

- Containerizzazione per deploy più semplice
- Implementazione a livello backend di avere la possibilità di più sedi fisiche
- Autenticazione a più fattori

# **12. DIRITTI D' AUTORE**

Il codice è attualmente privato, non distribuito con alcuna licenza.

Tuttavia, saremmo interessati a distribuirlo con licenza Open Source

# 12. GDPR

Ancora da determinare dopo consultazioni con Stakeholder